<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTrendingCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'card_id',
        'date',
        'rank',
        'price',
        'price_change_percent',
        'currency',
    ];

    protected $casts = [
        'date' => 'date',
        'rank' => 'integer',
        'price' => 'decimal:2',
        'price_change_percent' => 'decimal:2',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
