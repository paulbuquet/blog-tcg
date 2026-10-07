<?php

namespace App\Jobs;

use App\Models\Card;
use App\Models\Game;
use App\Services\TCGApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchTrendingCardsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(public Game $game) {}

    public function handle(TCGApiService $api): void
    {
        $items = $api->topMovers($this->game->api_identifier, 'up', '24h', 10);

        $ranked = array_values(array_filter(
            $items,
            fn (array $item) => isset($item['market_price']) && $item['market_price'] !== null,
        ));

        $date = now()->toDateString();

        $this->game->dailyTrendingCards()
            ->whereDate('date', $date)
            ->delete();

        foreach (array_slice($ranked, 0, 10) as $index => $item) {
            $card = Card::updateOrCreate(
                [
                    'game_id' => $this->game->id,
                    'external_id' => (string) $item['card_id'],
                ],
                [
                    'name' => $item['name'],
                    'set_name' => $item['set_name'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                ],
            );

            $this->game->dailyTrendingCards()->create([
                'card_id' => $card->id,
                'date' => $date,
                'rank' => $index + 1,
                'price' => $item['market_price'],
                'price_change_percent' => $item['price_change'] ?? 0,
                'currency' => 'USD',
            ]);
        }
    }
}
