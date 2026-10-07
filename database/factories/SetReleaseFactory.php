<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\SetRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SetRelease>
 */
class SetReleaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'name' => fake()->words(3, true),
            'external_id' => (string) fake()->unique()->numberBetween(1000, 99999),
            'release_date' => fake()->dateTimeBetween('+1 month', '+8 months')->format('Y-m-d'),
            'announced_at' => now()->toDateString(),
            'processed_at' => null,
        ];
    }
}
