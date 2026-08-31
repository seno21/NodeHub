<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an audit log entry safely without breaking execution flow.
     */
    public static function log(
        string $event,
        string $description,
        ?array $properties = null,
        ?User $user = null
    ): ?AuditLog {
        if (! config('audit.enabled', true)) {
            return null;
        }

        try {
            $currentUser = $user ?? Auth::user();

            return AuditLog::create([
                'user_id' => $currentUser?->id,
                'user_name' => $currentUser?->name ?? $currentUser?->username ?? 'System',
                'event' => $event,
                'description' => $description,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => substr(Request::userAgent() ?? 'CLI/System', 0, 500),
                'properties' => $properties,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently or log error so background core operations are never disrupted
            Log::error('Failed to write audit log', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
