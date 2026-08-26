<?php

namespace App\Models;

use Database\Factories\ComputerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'ip_address', 'vnc_port', 'os_type', 'location', 'tags', 'description', 'vnc_password', 'ssh_port', 'ssh_user', 'ssh_password', 'refresh_command'])]
class Computer extends Model
{
    /** @use HasFactory<ComputerFactory> */
    use HasFactory;

    public const OS_TYPES = ['windows', 'linux'];

    protected function casts(): array
    {
        return [
            'vnc_port' => 'integer',
            'ssh_port' => 'integer',
            'vnc_password' => 'encrypted',
            'ssh_password' => 'encrypted',
        ];
    }

    /**
     * Tags associated with this computer.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tagsRelation(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'computer_tag');
    }

    /**
     * Remote actions assigned to this computer.
     *
     * @return BelongsToMany<RemoteAction, $this>
     */
    public function remoteActions(): BelongsToMany
    {
        return $this->belongsToMany(RemoteAction::class, 'action_computer')->withTimestamps();
    }
}
