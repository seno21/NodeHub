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
     * @return array{ssh: ?SSH2, success: boolean, message: string, latency_ms: int}
     */
    public function checkSshConnection(Computer $computer): array
    {
        $startTime = microtime(true);
        $port = $computer->ssh_port ?: 22;
        $user = $computer->ssh_user ?: 'xubuntu';
        $password = $computer->ssh_password;

        if (empty($password)) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'ssh' => null,
                'success' => false,
                'message' => "Password SSH belum disetting pada perangkat ini ($user@{$computer->ip_address}:$port)",
                'latency_ms' => $latency,
            ];
        }

        try {
            $ssh = new SSH2($computer->ip_address, $port, 5);

            if (!$ssh->login($user, $password)) {
                $latency = (int) round((microtime(true) - $startTime) * 1000);
                return [
                    'ssh' => null,
                    'success' => false,
                    'message' => "Autentikasi SSH gagal ($user@{$computer->ip_address}:$port) — Cek Password SSH",
                    'latency_ms' => $latency,
                ];
            }

            $latency = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'ssh' => $ssh,
                'success' => true,
                'message' => "Koneksi SSH Terhubung & Autentikasi Berhasil ($user@{$computer->ip_address}:$port)",
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            Log::warning("SSH connection check failed for {$computer->name} ({$computer->ip_address}): {$e->getMessage()}");

            return [
                'ssh' => null,
                'success' => false,
                'message' => "Koneksi SSH Gagal / Timeout ({$computer->ip_address}:$port): {$e->getMessage()}",
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
            $execLatency = (int) round((microtime(true) - $startTime) * 1000);
            $totalLatency = $check['latency_ms'] + $execLatency;

            $rawOutput = (string) $output;
            if ($pass !== '') {
                $rawOutput = str_replace($pass, '***', $rawOutput);
            }

            $outputText = trim($rawOutput) ?: 'Perintah berhasil dieksekusi (no stdout)';

            return [
                'computer_id' => $computer->id,
                'computer_name' => $computer->name,
                'ssh_check' => [
                    'success' => true,
                    'message' => $check['message'],
                    'latency_ms' => $check['latency_ms'],
                ],
                'execution' => [
                    'success' => true,
                    'message' => "Script berhasil dijalankan ({$execLatency}ms)",
                    'output' => $outputText,
                    'latency_ms' => $execLatency,
                ],
                'success' => true,
                'message' => "SUKSES ({$totalLatency}ms)",
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
