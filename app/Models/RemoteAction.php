<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'icon', 'description', 'command'])]
class RemoteAction extends Model
{
    use HasFactory;

    /**
     * The computers targeted by this remote action.
     */
    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'action_computer')->withTimestamps();
    }
}
