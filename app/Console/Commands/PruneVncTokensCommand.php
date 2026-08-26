<?php

namespace App\Console\Commands;

use App\Services\VncSessionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('vnc:prune-tokens')]
#[Description('Remove expired VNC session tokens')]
class PruneVncTokensCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(VncSessionService $sessions): int
    {
        $removed = $sessions->prune();

        $this->info("Removed {$removed} expired token(s).");

        return self::SUCCESS;
    }
}
