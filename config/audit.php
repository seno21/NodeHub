<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Log Retention Period (Days)
    |--------------------------------------------------------------------------
    |
    | Defines how many days of audit logs should be kept in the database.
    | Records older than this limit will be automatically pruned via Laravel's
    | Pruneable scheduler task (php artisan model:prune).
    |
    */
    'retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Audit Log Enabled Status
    |--------------------------------------------------------------------------
    |
    | Toggle audit logging functionality globally.
    |
    */
    'enabled' => (bool) env('AUDIT_LOG_ENABLED', true),
];
