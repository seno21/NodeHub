<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('vnc:prune-tokens')->everyMinute();

// Prune audit logs older than the retention period (default 30 days) daily
Schedule::command('audit:prune')->daily();
