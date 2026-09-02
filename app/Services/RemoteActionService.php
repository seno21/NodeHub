<?php

namespace App\Services;

use App\Models\Computer;
use App\Models\RemoteAction;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class RemoteActionService
{
    /**
     * Execute a remote action command across targeted computers with pre-flight SSH connection check.
     *
     * @return array<int, array{
     *   computer_id: int,
     *   computer_name: string,
     *   ssh_check: array{success: boolean, message: string, latency_ms: int},
     *   execution: ?array{success: boolean, message: string, output: ?string, latency_ms: int},
     *   success: boolean,
     *   message: string,
     *   output: ?string,
     *   latency_ms: int
     * }>
     */
    public function executeAction(RemoteAction $action, array $targetComputerIds = []): array
    {
        $query = $action->computers();

        if (!empty($targetComputerIds)) {
            $query->whereIn('computers.id', $targetComputerIds);
        }

        $computers = $query->get();
        $results = [];

        foreach ($computers as $computer) {
            $results[$computer->id] = $this->executeCommandOnComputer($computer, $action->command);
        }

        return $results;
    }

    /**
     * Perform pre-flight SSH connection & authentication check on a single computer.
     *
     * @return array{ssh: ?SSH2, success: boolean, message: string, error_type: string, latency_ms: int}
     */
    public function checkSshConnection(Computer $computer): array
    {
        $startTime = microtime(true);
        $port = (int) ($computer->ssh_port ?: 22);
        $user = $computer->ssh_user ?: 'xubuntu';
        $password = $computer->ssh_password;

        // Pre-check TCP socket to distinguish network/port errors from authentication failures
        $socket = @fsockopen($computer->ip_address, $port, $errno, $errstr, 2.0);
        if (!is_resource($socket)) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $errLower = strtolower($errstr ?: '');

            if ($errno === 111 || str_contains($errLower, 'refused')) {
                $errorType = 'port_closed';
                $msg = "PORT SSH TERTUTUP: Port {$port} pada {$computer->ip_address} tertutup / ditolak (Connection Refused). Service SSH belum berjalan atau diblokir firewall.";
            } elseif ($errno === 110 || str_contains($errLower, 'timed out')) {
                $errorType = 'timeout';
                $msg = "KONEKSI TIMEOUT: {$computer->ip_address}:{$port} tidak merespons dalam 2 detik. Alamat IP tidak terjangkau atau diblokir firewall.";
            } elseif (str_contains($errLower, 'route') || str_contains($errLower, 'unreachable')) {
                $errorType = 'host_unreachable';
                $msg = "HOST UNREACHABLE: Alamat IP {$computer->ip_address} tidak dapat dijangkau di jaringan.";
            } else {
                $errorType = 'connection_failed';
                $msg = "KONEKSI GAGAL: Tidak dapat terhubung ke {$computer->ip_address}:{$port} - " . ($errstr ?: "Error #{$errno}");
            }

            return [
                'ssh' => null,
                'success' => false,
                'error_type' => $errorType,
                'message' => $msg,
                'latency_ms' => $latency,
            ];
        }

        fclose($socket);

        if (empty($password)) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'ssh' => null,
                'success' => false,
                'error_type' => 'password_missing',
                'message' => "Password SSH belum diatur pada perangkat ini ($user@{$computer->ip_address}:$port)",
                'latency_ms' => $latency,
            ];
        }

        // TCP Socket connected! Now attempt SSH login to test credentials.
        try {
            $ssh = new SSH2($computer->ip_address, $port, 5);

            if (!$ssh->login($user, $password)) {
                $latency = (int) round((microtime(true) - $startTime) * 1000);
                return [
                    'ssh' => null,
                    'success' => false,
                    'error_type' => 'wrong_password',
                    'message' => "AUTENTIKASI GAGAL (SALAH PASSWORD): Username '$user' atau Password SSH salah pada {$computer->ip_address}:{$port}. Mohon periksa kembali kredensial SSH.",
                    'latency_ms' => $latency,
                ];
            }

            $latency = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'ssh' => $ssh,
                'success' => true,
                'error_type' => 'ok',
                'message' => "Koneksi SSH Terhubung & Autentikasi Berhasil ($user@{$computer->ip_address}:$port)",
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            Log::warning("SSH handshake failed for {$computer->name} ({$computer->ip_address}): {$e->getMessage()}");

            return [
                'ssh' => null,
                'success' => false,
                'error_type' => 'ssh_handshake_error',
                'message' => "ERROR HANDSHAKE SSH ({$computer->ip_address}:$port): {$e->getMessage()}",
                'latency_ms' => $latency,
            ];
        }
    }

    /**
     * Execute SSH command on a computer with pre-flight connection check.
     */
    public function executeCommandOnComputer(Computer $computer, string $command): array
    {
        // STEP 1: Pre-flight SSH Connection & Auth Check
        $check = $this->checkSshConnection($computer);

        if (!$check['success']) {
            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ssh_check' => [
                    'success' => false,
                    'message' => $check['message'],
                    'latency_ms' => $check['latency_ms'],
                ],
                'execution' => null,
                'success' => false,
                'message' => "[SSH CHECK GAGAL] " . $check['message'],
                'output' => null,
                'latency_ms' => $check['latency_ms'],
            ];
        }

        // STEP 2: Pre-check succeeded -> Proceed to execute custom shell script!
        $startTime = microtime(true);
        /** @var SSH2 $ssh */
        $ssh = $check['ssh'];

        $pass = (string) ($computer->ssh_password ?? '');
        $user = (string) ($computer->ssh_user ?? 'xubuntu');
        $ip = (string) ($computer->ip_address ?? '');

        // Automatically replace placeholders with this computer's stored credentials
        $finalCommand = str_replace(
            ['{password}', '{ssh_password}', '$SSH_PASS', '{user}', '{ssh_user}', '{ip}'],
            [$pass, $pass, $pass, $user, $user, $ip],
            $command
        );

        try {
            $output = $ssh->exec($finalCommand);
            $exitStatus = $ssh->getExitStatus();
            $stdError = $ssh->getStdError();
            $execLatency = (int) round((microtime(true) - $startTime) * 1000);
            $totalLatency = $check['latency_ms'] + $execLatency;

            $rawOutput = (string) $output;
            if (!empty($stdError)) {
                $rawOutput .= ($rawOutput !== '' ? "\n" : '') . '[STDERR] ' . trim($stdError);
            }

            if ($pass !== '') {
                $rawOutput = str_replace($pass, '***', $rawOutput);
            }

            $isSuccess = ($exitStatus === 0 || $exitStatus === false || $exitStatus === null);
            $outputText = trim($rawOutput) ?: ($isSuccess ? 'Perintah berhasil dieksekusi (no stdout)' : 'Perintah gagal tanpa output');

            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ssh_check' => [
                    'success' => true,
                    'message' => $check['message'],
                    'latency_ms' => $check['latency_ms'],
                ],
                'execution' => [
                    'success' => $isSuccess,
                    'message' => $isSuccess
                        ? "Script berhasil dijalankan ({$execLatency}ms)"
                        : "Script gagal dengan exit status {$exitStatus} ({$execLatency}ms)",
                    'output' => $outputText,
                    'latency_ms' => $execLatency,
                ],
                'success' => $isSuccess,
                'message' => $isSuccess ? "SUKSES ({$totalLatency}ms)" : "GAGAL (Exit status: {$exitStatus})",
                'output' => $outputText,
                'latency_ms' => $totalLatency,
            ];
        } catch (\Throwable $e) {
            $execLatency = (int) round((microtime(true) - $startTime) * 1000);
            $totalLatency = $check['latency_ms'] + $execLatency;

            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ssh_check' => [
                    'success' => true,
                    'message' => $check['message'],
                    'latency_ms' => $check['latency_ms'],
                ],
                'execution' => [
                    'success' => false,
                    'message' => "Error eksekusi script: {$e->getMessage()}",
                    'output' => null,
                    'latency_ms' => $execLatency,
                ],
                'success' => false,
                'message' => "Gagal eksekusi script: {$e->getMessage()}",
                'output' => null,
                'latency_ms' => $totalLatency,
            ];
        }
    }
}
