<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'set_release_id',
        'type',
        'title',
        'slug',
        'content',
        'status',
        'published_at',
    ];

    protected $casts = [
        'type' => ArticleType::class,
        'status' => ArticleStatus::class,
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (empty($article->slug) && filled($article->title)) {
                $article->slug = Str::slug($article->title).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function setRelease(): BelongsTo
    {
        return $this->belongsTo(SetRelease::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at');
    }

    public function publish(): void
    {
        $this->status = ArticleStatus::Published;
        $this->published_at = now();
        $this->save();
    }
}
