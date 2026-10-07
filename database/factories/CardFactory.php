<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'external_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'name' => fake()->words(3, true),
            'set_name' => fake()->words(3, true),
            'image_url' => fake()->imageUrl(),
        ];
    }
}
