<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'set_release_id' => null,
            'type' => ArticleType::TcgNews,
            'title' => fake()->sentence(6),
            'slug' => null,
            'content' => fake()->paragraphs(5, true),
            'status' => ArticleStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function releaseAnnouncement(): static
    {
        return $this->state(fn () => ['type' => ArticleType::ReleaseAnnouncement]);
    }
}
