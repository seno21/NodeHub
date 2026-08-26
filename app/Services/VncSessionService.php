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
     * Complete diagnostic probe for dedicated "Cek Ping" button (ICMP + VNC Port + SSH Port).
     *
     * @return array{
     *   online: boolean,
     *   latency_ms: ?float,
     *   vnc_ok: boolean,
     *   vnc_latency: ?float,
     *   ssh_ok: boolean,
     *   ssh_latency: ?float,
     *   icmp_ok: boolean
     * }
     */
    public function pingDiagnostics(Computer $computer): array
    {
        $vncLatency = $this->probe($computer);

        $start = microtime(true);
        $sshPort = (int) ($computer->ssh_port ?: 22);
        $sshSocket = @fsockopen($computer->ip_address, $sshPort, $errno, $errstr, 1);
        $sshOk = is_resource($sshSocket);
        if ($sshOk) {
            fclose($sshSocket);
        }
        $sshLatency = $sshOk ? max(0.1, round((microtime(true) - $start) * 1000, 1)) : null;

        $icmpOk = false;
        if (! app()->environment('testing')) {
            $ip = escapeshellarg($computer->ip_address);
            exec("ping -c 1 -W 1 {$ip} 2>&1", $output, $resultCode);
            $icmpOk = ($resultCode === 0);
        }

        $online = $vncLatency !== null || $sshOk || $icmpOk;

        return [
            'online' => $online,
            'latency_ms' => $vncLatency ?? $sshLatency ?? ($icmpOk ? 1.0 : null),
            'vnc_ok' => $vncLatency !== null,
            'vnc_latency' => $vncLatency,
            'ssh_ok' => $sshOk,
            'ssh_latency' => $sshLatency,
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
        $path = config('vnc.websockify.token_file');
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // TokenFile format expected by websockify: "<token>: <host>:<port>"
        $line = sprintf('%s: %s:%d', $token, $computer->ip_address, (int) $computer->vnc_port);

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

    private function cacheKey(string $token): string
    {
        return "vnc-session:{$token}";
    }
}
