<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'event',
        'description',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Prune audit log records older than the specified retention days.
     */
    public static function pruneOldLogs(?int $days = null): int
    {
        $days = $days ?? (int) config('audit.retention_days', 30);

        return static::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Get the user that triggered the audit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query to search across event, description, user_name, or IP address.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('event', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }

    /**
     * Scope query by event type.
     */
    public function scopeEvent(Builder $query, ?string $event): Builder
    {
        if (empty($event) || $event === 'all') {
            return $query;
        }

        return $query->where('event', $event);
    }

    /**
     * Scope query by category prefix (e.g. auth, computer, action, vnc, profile).
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (empty($category) || $category === 'all') {
            return $query;
        }

        return $query->where('event', 'like', "{$category}.%");
    }

    /**
     * Scope query by user ID.
     */
    public function scopeUser(Builder $query, ?int $userId): Builder
    {
        if (empty($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }
}
