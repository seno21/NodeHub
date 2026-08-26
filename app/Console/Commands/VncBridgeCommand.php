<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

#[Signature('vnc:bridge {--daemon : Run websockify in the background}')]
#[Description('Start the websockify bridge used by the noVNC viewer')]
class VncBridgeCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $binary = trim((string) shell_exec('command -v '.escapeshellarg(config('vnc.websockify.binary'))));

        if ($binary === '') {
            $this->error('websockify binary not found. Install it or set VNC_WEBSOCKIFY_BINARY.');

            return self::FAILURE;
        }

        $tokenFile = config('vnc.websockify.token_file');
        $directory = dirname($tokenFile);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! is_file($tokenFile)) {
            touch($tokenFile);
        }

        $command = [
            $binary,
            '--log-file='.storage_path('logs/websockify.log'),
            '--token-plugin',
            'TokenFile',
            '--token-source',
            $tokenFile,
        ];

        if ($this->option('daemon')) {
            $command[] = '--daemon';
        }

        $command[] = (string) config('vnc.websockify.listen');

        if (! $this->option('daemon')) {
            $this->info('Press Ctrl+C to stop the bridge.');

            $process = new Process($command);
            $process->setTimeout(null);
            $exitCode = $process->run(function (string $type, string $buffer): void {
                $this->output->write($buffer);
            });

            return $exitCode ?? self::SUCCESS;
        }

        // In daemon mode the forked websockify child would keep stdout/stderr
        // pipes open forever; redirect them so run() returns once the parent exits.
        $shellCommand = implode(' ', array_map('escapeshellarg', $command)).' > /dev/null 2>&1';

        Process::fromShellCommandline($shellCommand)->setTimeout(null)->mustRun();

        sleep(1);

        if ($this->isListening()) {
            $this->info('websockify is running on '.config('vnc.websockify.listen'));

            return self::SUCCESS;
        }

        $this->error('websockify failed to start. Check storage/logs/websockify.log');

        return self::FAILURE;
    }

    private function isListening(): bool
    {
        [, $port] = array_pad(explode(':', (string) config('vnc.websockify.listen'), 2), 2, '6080');
        $socket = @fsockopen('127.0.0.1', (int) $port, $errno, $errstr, 2);

        if (is_resource($socket)) {
            fclose($socket);

            return true;
        }

        return false;
    }
}
