<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'api_identifier',
        'is_active',
        'banner_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function dailyTrendingCards(): HasMany
    {
        return $this->hasMany(DailyTrendingCard::class);
    }

    public function setReleases(): HasMany
    {
        return $this->hasMany(SetRelease::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Classes Tailwind (badge, dégradé, point) propres au jeu.
     *
     * @return array{badge: string, gradient: string, dot: string}
     */
    public function accent(): array
    {
        return match ($this->slug) {
            'pokemon' => [
                'badge' => 'bg-red-500/10 text-red-400 ring-red-500/30',
                'gradient' => 'from-red-500 via-rose-400 to-amber-400',
                'dot' => 'bg-red-400',
            ],
            'magic' => [
                'badge' => 'bg-violet-500/10 text-violet-400 ring-violet-500/30',
                'gradient' => 'from-violet-500 via-indigo-400 to-cyan-400',
                'dot' => 'bg-violet-400',
            ],
            'yugioh' => [
                'badge' => 'bg-amber-500/10 text-amber-400 ring-amber-500/30',
                'gradient' => 'from-amber-400 via-yellow-300 to-orange-400',
                'dot' => 'bg-amber-400',
            ],
            'one-piece' => [
                'badge' => 'bg-orange-500/10 text-orange-400 ring-orange-500/30',
                'gradient' => 'from-orange-500 via-red-400 to-rose-500',
                'dot' => 'bg-orange-400',
            ],
            default => static::defaultAccent(),
        };
    }

    /**
     * Classes Tailwind de repli pour un jeu sans thème dédié.
     *
     * @return array{badge: string, gradient: string, dot: string}
     */
    public static function defaultAccent(): array
    {
        return [
            'badge' => 'bg-indigo-500/10 text-indigo-400 ring-indigo-500/30',
            'gradient' => 'from-indigo-500 via-violet-400 to-fuchsia-400',
            'dot' => 'bg-indigo-400',
        ];
    }
}
