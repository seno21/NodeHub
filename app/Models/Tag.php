<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    /**
     * The computers that belong to the tag.
     *
     * @return BelongsToMany<Computer, $this>
     */
    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_tag');
    }
}
