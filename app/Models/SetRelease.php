<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SetRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'external_id',
        'release_date',
        'announced_at',
        'processed_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'announced_at' => 'date',
        'processed_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
