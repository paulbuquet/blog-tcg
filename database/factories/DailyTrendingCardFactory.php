<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\DailyTrendingCard;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyTrendingCard>
 */
class DailyTrendingCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'card_id' => Card::factory(),
            'date' => now()->toDateString(),
            'rank' => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 0.5, 500),
            'price_change_percent' => fake()->randomFloat(2, -30, 200),
            'currency' => 'USD',
        ];
    }
}
