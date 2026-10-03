<?php

namespace App\Services;

use App\Models\Computer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VncSessionService
{
    /**
     * Create an ephemeral VNC session for the given computer.
     *
     * Returns the session token used by noVNC to connect through websockify.
     */
    public function createSession(Computer $computer): string
    {
        $token = Str::random(40);

        Cache::put(
            $this->cacheKey($token),
            [
                'name' => $computer->name,
                'ip_address' => $computer->ip_address,
                'vnc_port' => (int) $computer->vnc_port,
                'os_type' => $computer->os_type,
                'vnc_password' => $computer->vnc_password,
            ],
            now()->addSeconds(config('vnc.token_ttl')),
        );

        $this->appendTokenLine($token, $computer);

        return $token;
    }

    /**
     * Create an ephemeral VNC session for a direct IP connection (Fast Connect).
     *
     * Returns the session token used by noVNC to connect through websockify.
     */
    public function createDirectSession(
        string $ipAddress,
        int $vncPort = 5900,
        ?string $vncPassword = null,
        ?string $name = null,
        string $osType = 'linux',
    ): string {
        $token = Str::random(40);
        $displayName = $name ?: "Fast Connect ({$ipAddress}:{$vncPort})";

        Cache::put(
            $this->cacheKey($token),
            [
                'name' => $displayName,
                'ip_address' => $ipAddress,
                'vnc_port' => $vncPort,
                'os_type' => $osType,
                'vnc_password' => $vncPassword,
                'is_fast_connect' => true,
            ],
            now()->addSeconds(config('vnc.token_ttl')),
        );

        $this->appendTokenLineDirect($token, $ipAddress, $vncPort);

        return $token;
    }

    /**
     * Get the stored session data for a token, or null when expired.
     *
     * @return array{ip_address: string, vnc_port: int, os_type: string, vnc_password: string|null}|null
     */
    public function getSession(string $token): ?array
    {
        /** @var array{ip_address: string, vnc_port: int, os_type: string, vnc_password: string|null}|null */
        return Cache::get($this->cacheKey($token));
    }

    /**
     * Remove expired tokens from the websockify token file.
     *
     * Returns the number of removed entries.
     */
    public function prune(): int
    {
        $path = config('vnc.websockify.token_file');

        if (! is_file($path)) {
            return 0;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $kept = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '#')) {
                continue;
            }

            $token = trim(strtok($line, ':') ?: '');

            if ($token !== '' && Cache::has($this->cacheKey($token))) {
                $kept[] = $line;
            }
        }

        $removed = count($lines) - count($kept);
        $this->writeTokenFile($kept);

        return $removed;
    }

    /**
     * Probe the computer's VNC port and measure latency.
     *
     * Returns round-trip time in milliseconds, or null when unreachable.
     */
    public function probe(Computer $computer): ?float
    {
        $start = microtime(true);

        $socket = @fsockopen(
            $computer->ip_address,
            $computer->vnc_port,
            $errno,
            $errstr,
            (float) config('vnc.status_timeout'),
        );

        if (! is_resource($socket)) {
            return null;
        }

        fclose($socket);

        return round((microtime(true) - $start) * 1000, 1);
    }

    /**
     * Complete diagnostic probe for dedicated "Cek Ping" button (ICMP + VNC Port + SSH Port & Auth).
     *
     * @return array{
     *   online: boolean,
     *   latency_ms: ?float,
     *   vnc_ok: boolean,
     *   vnc_latency: ?float,
     *   vnc_error_type: string,
     *   vnc_error_message: string,
     *   ssh_ok: boolean,
     *   ssh_latency: ?float,
     *   ssh_auth_ok: boolean,
     *   ssh_error_type: string,
     *   ssh_error_message: string,
     *   icmp_ok: boolean
     * }
     */
    public function pingDiagnostics(Computer $computer): array
    {
        // 1. Detailed VNC Probe
        $vncStart = microtime(true);
        $vncPort = (int) ($computer->vnc_port ?: 5900);
        $vncSocket = @fsockopen($computer->ip_address, $vncPort, $vncErrno, $vncErrstr, (float) config('vnc.status_timeout', 1.0));
        $vncOk = is_resource($vncSocket);
        $vncLatency = null;
        $vncErrorType = 'ok';
        $vncErrorMessage = 'Port VNC Terhubung & Service Responding';

        if ($vncOk) {
            fclose($vncSocket);
            $vncLatency = round((microtime(true) - $vncStart) * 1000, 1);
        } else {
            $errLower = strtolower($vncErrstr ?: '');
            if ($vncErrno === 111 || str_contains($errLower, 'refused')) {
                $vncErrorType = 'port_closed';
                $vncErrorMessage = "Port VNC ({$vncPort}) Tertutup (Connection Refused)";
            } elseif ($vncErrno === 110 || str_contains($errLower, 'timed out')) {
                $vncErrorType = 'timeout';
                $vncErrorMessage = "Koneksi VNC Timeout (Port {$vncPort} tidak merespons)";
            } elseif (str_contains($errLower, 'route') || str_contains($errLower, 'unreachable')) {
                $vncErrorType = 'host_unreachable';
                $vncErrorMessage = "Host / IP address {$computer->ip_address} tidak dapat dijangkau";
            } else {
                $vncErrorType = 'connection_failed';
                $vncErrorMessage = "Port VNC tidak merespons: " . ($vncErrstr ?: "Error #{$vncErrno}");
            }
        }

        // 2. Detailed SSH Probe & Auth check
        /** @var RemoteActionService $actionService */
        $actionService = app(RemoteActionService::class);
        $sshCheck = $actionService->checkSshConnection($computer);

        $sshOk = $sshCheck['error_type'] !== 'port_closed' && $sshCheck['error_type'] !== 'timeout' && $sshCheck['error_type'] !== 'host_unreachable' && $sshCheck['error_type'] !== 'connection_failed';
        $sshAuthOk = $sshCheck['success'];
        $sshLatency = $sshCheck['latency_ms'] ? (float) $sshCheck['latency_ms'] : null;

        // 3. ICMP Ping
        $icmpOk = false;
        if (! app()->environment('testing')) {
            $ip = escapeshellarg($computer->ip_address);
            exec("ping -c 1 -W 1 {$ip} 2>&1", $output, $resultCode);
            $icmpOk = ($resultCode === 0);
        }

        $online = $vncOk || $sshOk || $icmpOk;

        return [
            'online' => $online,
            'latency_ms' => $vncLatency ?? $sshLatency ?? ($icmpOk ? 1.0 : null),
            'vnc_ok' => $vncOk,
            'vnc_latency' => $vncLatency,
            'vnc_error_type' => $vncErrorType,
            'vnc_error_message' => $vncErrorMessage,
            'ssh_ok' => $sshOk,
            'ssh_latency' => $sshLatency,
            'ssh_auth_ok' => $sshAuthOk,
            'ssh_error_type' => $sshCheck['error_type'],
            'ssh_error_message' => $sshCheck['message'],
            'icmp_ok' => $icmpOk,
        ];
    }

    /**
     * Check whether the computer's VNC port accepts TCP connections.
     */
    public function isReachable(Computer $computer): bool
    {
        return $this->probe($computer) !== null;
    }

    /**
     * Check whether the websockify gateway is listening.
     */
    public function isBridgeUp(): bool
    {
        [$host, $port] = array_pad(
            explode(':', (string) config('vnc.websockify.listen'), 2),
            2,
            null,
        );

        $socket = @fsockopen(
            $host === '0.0.0.0' || $host === '' ? '127.0.0.1' : (string) $host,
            (int) ($port ?? 6080),
            $errno,
            $errstr,
            1,
        );

        if (is_resource($socket)) {
            fclose($socket);

            return true;
        }

        return false;
    }

    private function appendTokenLine(string $token, Computer $computer): void
    {
        $this->appendTokenLineDirect($token, $computer->ip_address, (int) $computer->vnc_port);
    }

    private function appendTokenLineDirect(string $token, string $ipAddress, int $vncPort): void
    {
        $path = config('vnc.websockify.token_file');
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // TokenFile format expected by websockify: "<token>: <host>:<port>"
        $line = sprintf('%s: %s:%d', $token, $ipAddress, $vncPort);

        file_put_contents($path, $line.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function writeTokenFile(array $lines): void
    {
        $path = config('vnc.websockify.token_file');

        if ($lines === []) {
            if (is_file($path)) {
                unlink($path);
            }

            return;
        }

        $tmp = $path.'.tmp';

        file_put_contents($tmp, implode(PHP_EOL, $lines).PHP_EOL, LOCK_EX);
        rename($tmp, $path);
    }

    /**
     * Send native VNC RFB keypress (e.g. F5 = 0xffc2) directly via VNC TCP protocol to target computer without SSH.
     *
     * @return array{success: boolean, message: string, latency_ms: int}
     */
    public function sendVncKey(Computer $computer, int $keysym = 0xffc2): array
    {
        $startTime = microtime(true);
        $host = $computer->ip_address;
        $port = (int) ($computer->vnc_port ?: 5900);
        $password = $computer->vnc_password;
        $timeout = 2.5;

        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (! is_resource($socket)) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $errLower = strtolower($errstr ?: '');
            if ($errno === 111 || str_contains($errLower, 'refused')) {
                $msg = "VNC PORT CLOSED: Port {$port} on {$host} is refused. Ensure VNC server is running.";
            } elseif ($errno === 110 || str_contains($errLower, 'timed out')) {
                $msg = "CONNECTION TIMEOUT: {$host}:{$port} did not respond.";
            } else {
                $msg = "Koneksi VNC gagal ke {$host}:{$port} - " . ($errstr ?: "Error #{$errno}");
            }

            return [
                'success' => false,
                'message' => $msg,
                'latency_ms' => $latency,
            ];
        }

        stream_set_timeout($socket, 2, 500000);

        // 1. RFB ProtocolVersion Handshake
        $serverProto = fread($socket, 12);
        if ($serverProto === false || strlen($serverProto) < 12) {
            fclose($socket);
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'message' => "VNC Handshake gagal: Respon server tidak valid pada {$host}:{$port}",
                'latency_ms' => $latency,
            ];
        }

        fwrite($socket, "RFB 003.008\n");

        // 2. Security Handshake
        $secNumData = fread($socket, 1);
        if ($secNumData === false || strlen($secNumData) < 1) {
            fclose($socket);
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'message' => "VNC Security handshake gagal pada {$host}:{$port}",
                'latency_ms' => $latency,
            ];
        }

        $secNum = ord($secNumData);
        if ($secNum === 0) {
            $reasonLenData = fread($socket, 4);
            $reasonLen = (is_string($reasonLenData) && strlen($reasonLenData) === 4) ? unpack('N', $reasonLenData)[1] : 0;
            $reason = $reasonLen > 0 ? fread($socket, $reasonLen) : 'Security handshake failed';
            fclose($socket);
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'message' => "VNC Security error ({$host}:{$port}): " . ($reason ?: 'Failed'),
                'latency_ms' => $latency,
            ];
        }

        $secTypes = fread($socket, $secNum);
        if ($secTypes === false) {
            $secTypes = "\x01";
        }

        $selectedType = 0;
        if (str_contains($secTypes, "\x01")) {
            // Type 1 = None
            $selectedType = 1;
            fwrite($socket, "\x01");
        } elseif (str_contains($secTypes, "\x02")) {
            // Type 2 = VNC Auth
            $selectedType = 2;
            fwrite($socket, "\x02");
        } else {
            $selectedType = ord($secTypes[0]);
            fwrite($socket, chr($selectedType));
        }

        if ($selectedType === 2) {
            if (empty($password)) {
                fclose($socket);
                $latency = (int) round((microtime(true) - $startTime) * 1000);

                return [
                    'success' => false,
                    'message' => "VNC Auth error: Password VNC belum diatur di portal untuk {$computer->name} ({$host}:{$port})",
                    'latency_ms' => $latency,
                ];
            }

            $challenge = fread($socket, 16);
            if ($challenge === false || strlen($challenge) < 16) {
                fclose($socket);
                $latency = (int) round((microtime(true) - $startTime) * 1000);

                return [
                    'success' => false,
                    'message' => "VNC Auth challenge gagal dari {$host}:{$port}",
                    'latency_ms' => $latency,
                ];
            }

            $response = $this->vncEncryptPassword($password, $challenge);
            fwrite($socket, $response);

            $authResult = fread($socket, 4);
            $resultVal = (is_string($authResult) && strlen($authResult) === 4) ? unpack('N', $authResult)[1] : 1;
            if ($resultVal !== 0) {
                fclose($socket);
                $latency = (int) round((microtime(true) - $startTime) * 1000);

                return [
                    'success' => false,
                    'message' => "VNC Password salah / ditolak server {$host}:{$port}",
                    'latency_ms' => $latency,
                ];
            }
        } elseif ($selectedType !== 1) {
            $authResult = fread($socket, 4);
            $resultVal = (is_string($authResult) && strlen($authResult) === 4) ? unpack('N', $authResult)[1] : 1;
            if ($resultVal !== 0) {
                fclose($socket);
                $latency = (int) round((microtime(true) - $startTime) * 1000);

                return [
                    'success' => false,
                    'message' => "VNC Auth gagal untuk {$host}:{$port}",
                    'latency_ms' => $latency,
                ];
            }
        } else {
            // Type 1 in RFB 3.8 receives 4-byte SecurityResult
            fread($socket, 4);
        }

        // 3. ClientInit (Shared = 1)
        fwrite($socket, "\x01");

        // Read ServerInit (24 bytes header + nameLen bytes)
        $serverInit = fread($socket, 24);
        if (is_string($serverInit) && strlen($serverInit) >= 24) {
            $nameLen = unpack('N', substr($serverInit, 20, 4))[1] ?? 0;
            if ($nameLen > 0) {
                fread($socket, $nameLen);
            }
        }

        // 4. Send RFB KeyEvent Message (F5 = 0xffc2)
        // KeyDown (8 bytes): type=4, down=1, pad=0, keysym (N)
        $keyDown = pack('CCnN', 4, 1, 0, $keysym);
        // KeyUp (8 bytes): type=4, down=0, pad=0, keysym (N)
        $keyUp = pack('CCnN', 4, 0, 0, $keysym);

        fwrite($socket, $keyDown);
        usleep(40000); // 40ms hold
        fwrite($socket, $keyUp);

        fclose($socket);
        $latency = (int) round((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'message' => "VNC RFB F5 Keypress berhasil terkirim via VNC protocol ({$latency}ms)",
            'latency_ms' => $latency,
        ];
    }

    private function vncEncryptPassword(string $password, string $challenge): string
    {
        $key = str_pad(substr($password, 0, 8), 8, "\0");
        $reversedKey = '';
        for ($i = 0; $i < 8; $i++) {
            $b = ord($key[$i]);
            $b = (($b & 0xF0) >> 4) | (($b & 0x0F) << 4);
            $b = (($b & 0xCC) >> 2) | (($b & 0x33) << 2);
            $b = (($b & 0xAA) >> 1) | (($b & 0x55) << 1);
            $reversedKey .= chr($b);
        }

        if (function_exists('openssl_encrypt')) {
            return (string) openssl_encrypt($challenge, 'DES-ECB', $reversedKey, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        }

        return $challenge;
    }

    private function cacheKey(string $token): string
    {
        return "vnc-session:{$token}";
    }
}
