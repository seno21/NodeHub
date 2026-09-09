<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:prune {--days= : Override retention period in days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune audit logs older than the configured retention period (default 30 days)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('audit.retention_days', 30);

        $this->info("Pruning audit logs (Retention: {$days} days)...");

        $count = AuditLog::pruneOldLogs($days);

        $this->info("Successfully pruned {$count} old audit log records.");

        return Command::SUCCESS;
    }
}
