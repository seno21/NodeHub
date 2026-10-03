<?php

namespace App\Services;

use App\Models\Computer;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\DES;

class VncActionService
{
    /**
     * Execute VNC F5 key refresh across target computers directly via RFB protocol (WITHOUT SSH).
     *
     * @param array<int> $targetComputerIds
     * @return array<int, array{
     *   computer_id: int,
     *   computer_name: string,
     *   ip_address: string,
     *   vnc_port: int,
     *   success: boolean,
     *   message: string,
     *   latency_ms: int
     * }>
     */
    public function executeBatchF5Refresh(array $targetComputerIds = []): array
    {
        $query = Computer::query();

        if (!empty($targetComputerIds)) {
            $query->whereIn('id', $targetComputerIds);
        }

        $computers = $query->get();
        $results = [];

        foreach ($computers as $computer) {
            $results[$computer->id] = $this->sendF5RefreshToComputer($computer);
        }

        return $results;
    }

    /**
     * Connect directly to VNC RFB server on IP:Port and send F5 keypress events (KeyDown & KeyUp).
     *
     * NO SSH is used. Uses native RFB 3.x socket handshake and DES authentication if required.
     *
     * @return array{
     *   computer_id: int,
     *   computer_name: string,
     *   ip_address: string,
     *   vnc_port: int,
     *   success: boolean,
     *   message: string,
     *   latency_ms: int
     * }
     */
    public function sendF5RefreshToComputer(Computer $computer, int $keySym = 0xFFC2): array
    {
        $startTime = microtime(true);
        $host = $computer->ip_address;
        $port = (int) ($computer->vnc_port ?: 5900);
        $password = $computer->vnc_password;

        $socket = @fsockopen($host, $port, $errno, $errstr, 2.5);
        if (!is_resource($socket)) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $errLower = strtolower($errstr ?: '');
            if ($errno === 111 || str_contains($errLower, 'refused')) {
                $msg = "PORT VNC TERTUTUP: Port {$port} pada {$host} ditolak (Connection Refused). Service VNC server mungkin belum berjalan.";
            } elseif ($errno === 110 || str_contains($errLower, 'timed out')) {
                $msg = "KONEKSI TIMEOUT: Target {$host}:{$port} tidak merespons dalam 2.5 detik.";
            } else {
                $msg = "KONEKSI GAGAL: Tidak dapat membuka socket ke {$host}:{$port} - " . ($errstr ?: "Error #{$errno}");
            }

            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ip_address' => $host,
                'vnc_port' => $port,
                'success' => false,
                'message' => $msg,
                'latency_ms' => $latency,
            ];
        }

        stream_set_timeout($socket, 3);

        try {
            // 1. Handshake Protocol Version (12 bytes)
            $serverVersionStr = @fread($socket, 12);
            if (!$serverVersionStr || strlen($serverVersionStr) < 12) {
                return [
                    'computer_id' => $computer->id,
                    'computer_name' => $computer->name,
                    'ip_address' => $host,
                    'vnc_port' => $port,
                    'success' => false,
                    'message' => "HANDSHAKE ERROR: Tidak menerima version string dari VNC Server {$host}:{$port}",
                    'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                ];
            }

            // Echo version string back to VNC server
            @fwrite($socket, $serverVersionStr);

            $isRfb38 = str_contains($serverVersionStr, '003.008');
            $isRfb37 = str_contains($serverVersionStr, '003.007');

            if ($isRfb38 || $isRfb37) {
                $numTypesByte = @fread($socket, 1);
                if (!$numTypesByte) {
                    return [
                        'computer_id' => $computer->id,
                        'computer_name' => $computer->name,
                        'ip_address' => $host,
                        'vnc_port' => $port,
                        'success' => false,
                        'message' => "RFB ERROR: Gagal membaca security types dari VNC server",
                        'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                    ];
                }
                $numTypes = ord($numTypesByte);
                if ($numTypes === 0) {
                    $reasonLenData = @fread($socket, 4);
                    $reasonLen = $reasonLenData ? unpack('N', $reasonLenData)[1] : 0;
                    $reason = $reasonLen > 0 ? @fread($socket, $reasonLen) : 'Server rejected connection';
                    return [
                        'computer_id' => $computer->id,
                        'computer_name' => $computer->name,
                        'ip_address' => $host,
                        'vnc_port' => $port,
                        'success' => false,
                        'message' => "VNC ERROR: {$reason}",
                        'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                    ];
                }

                $types = @fread($socket, $numTypes);
                $typeArray = array_map('ord', str_split($types ?: ''));

                if (in_array(1, $typeArray, true)) { // Security Type 1: None (No Auth)
                    @fwrite($socket, "\x01");
                    if ($isRfb38) {
                        $secResultData = @fread($socket, 4);
                        $secResult = $secResultData ? unpack('N', $secResultData)[1] : 1;
                        if ($secResult !== 0) {
                            return [
                                'computer_id' => $computer->id,
                                'computer_name' => $computer->name,
                                'ip_address' => $host,
                                'vnc_port' => $port,
                                'success' => false,
                                'message' => "VNC AUTH ERROR: Security result failure (Code: {$secResult})",
                                'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                            ];
                        }
                    }
                } elseif (in_array(2, $typeArray, true)) { // Security Type 2: VNC Authentication
                    if (empty($password)) {
                        return [
                            'computer_id' => $computer->id,
                            'computer_name' => $computer->name,
                            'ip_address' => $host,
                            'vnc_port' => $port,
                            'success' => false,
                            'message' => "VNC AUTH FAILED: VNC Server memerlukan password, namun password VNC belum diatur untuk {$computer->name}",
                            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                        ];
                    }

                    @fwrite($socket, "\x02");
                    $challenge = @fread($socket, 16);
                    if (!$challenge || strlen($challenge) < 16) {
                        return [
                            'computer_id' => $computer->id,
                            'computer_name' => $computer->name,
                            'ip_address' => $host,
                            'vnc_port' => $port,
                            'success' => false,
                            'message' => "VNC AUTH ERROR: Gagal membaca 16-byte auth challenge",
                            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                        ];
                    }

                    $desKey = '';
                    $paddedPass = str_pad(substr($password, 0, 8), 8, "\0");
                    for ($i = 0; $i < 8; $i++) {
                        $desKey .= chr($this->reverseBits(ord($paddedPass[$i])));
                    }

                    $des = new DES('ecb');
                    $des->setKey($desKey);
                    $des->disablePadding();
                    $response = $des->encrypt($challenge);

                    @fwrite($socket, $response);
                    $secResultData = @fread($socket, 4);
                    $secResult = $secResultData ? unpack('N', $secResultData)[1] : 1;

                    if ($secResult !== 0) {
                        return [
                            'computer_id' => $computer->id,
                            'computer_name' => $computer->name,
                            'ip_address' => $host,
                            'vnc_port' => $port,
                            'success' => false,
                            'message' => "VNC AUTH FAILED: Password VNC salah pada {$host}:{$port}",
                            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                        ];
                    }
                } else {
                    return [
                        'computer_id' => $computer->id,
                        'computer_name' => $computer->name,
                        'ip_address' => $host,
                        'vnc_port' => $port,
                        'success' => false,
                        'message' => "VNC ERROR: Security type tidak didukung oleh server (Types: " . implode(',', $typeArray) . ")",
                        'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                    ];
                }
            } else { // RFB 3.3
                $secTypeData = @fread($socket, 4);
                $secType = $secTypeData ? unpack('N', $secTypeData)[1] : 0;

                if ($secType === 1) {
                    // No Auth
                } elseif ($secType === 2) {
                    if (empty($password)) {
                        return [
                            'computer_id' => $computer->id,
                            'computer_name' => $computer->name,
                            'ip_address' => $host,
                            'vnc_port' => $port,
                            'success' => false,
                            'message' => "VNC AUTH FAILED: Password VNC belum diatur",
                            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                        ];
                    }
                    $challenge = @fread($socket, 16);
                    $desKey = '';
                    $paddedPass = str_pad(substr($password, 0, 8), 8, "\0");
                    for ($i = 0; $i < 8; $i++) {
                        $desKey .= chr($this->reverseBits(ord($paddedPass[$i])));
                    }
                    $des = new DES('ecb');
                    $des->setKey($desKey);
                    $des->disablePadding();
                    $response = $des->encrypt($challenge);

                    @fwrite($socket, $response);
                    $secResultData = @fread($socket, 4);
                    $secResult = $secResultData ? unpack('N', $secResultData)[1] : 1;
                    if ($secResult !== 0) {
                        return [
                            'computer_id' => $computer->id,
                            'computer_name' => $computer->name,
                            'ip_address' => $host,
                            'vnc_port' => $port,
                            'success' => false,
                            'message' => "VNC AUTH FAILED: Password VNC salah",
                            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                        ];
                    }
                }
            }

            // 2. ClientInit Message (1 byte: Shared Desktop)
            @fwrite($socket, "\x01");

            // 3. ServerInit Message (24 bytes header + desktop name)
            $serverInitHeader = @fread($socket, 24);
            if ($serverInitHeader && strlen($serverInitHeader) >= 24) {
                $nameLen = unpack('N', substr($serverInitHeader, 20, 4))[1];
                if ($nameLen > 0) {
                    @fread($socket, $nameLen);
                }
            }

            // 4. RFB KeyEvent Protocol: Send F5 KeyDown and KeyUp
            // Message Type 4 (KeyEvent), Down flag 1/0, Padding 0, KeySym 0xFFC2 (XK_F5)
            $keyDown = pack('CCnN', 4, 1, 0, $keySym);
            $keyUp = pack('CCnN', 4, 0, 0, $keySym);

            @fwrite($socket, $keyDown);
            @fflush($socket);
            usleep(50000); // 50ms press duration

            @fwrite($socket, $keyUp);
            @fflush($socket);
            usleep(20000);

            $latency = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ip_address' => $host,
                'vnc_port' => $port,
                'success' => true,
                'message' => "SUKSES: F5 Refresh terkirim via VNC (RFB Protocol, Tanpa SSH) dalam {$latency}ms",
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            Log::error("VNC F5 refresh error for {$host}:{$port}: " . $e->getMessage());

            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ip_address' => $host,
                'vnc_port' => $port,
                'success' => false,
                'message' => "ERROR VNC RFB: {$e->getMessage()}",
                'latency_ms' => $latency,
            ];
        } finally {
            @fclose($socket);
        }
    }

    /**
     * Bit reversal helper for 8-bit integers (VNC DES key construction).
     */
    private function reverseBits(int $b): int
    {
        $b = (($b & 0xF0) >> 4) | (($b & 0x0F) << 4);
        $b = (($b & 0xCC) >> 2) | (($b & 0x33) << 2);
        $b = (($b & 0xAA) >> 1) | (($b & 0x55) << 1);
        return $b;
    }
}
