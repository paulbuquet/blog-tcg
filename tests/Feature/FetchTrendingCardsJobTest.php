<?php

namespace Tests\Feature;

use App\Jobs\FetchTrendingCardsJob;
use App\Models\Card;
use App\Models\DailyTrendingCard;
use App\Models\Game;
use App\Services\TCGApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchTrendingCardsJobTest extends TestCase
{
    use RefreshDatabase;

    private function fakeTopMovers(array $cards): void
    {
        Http::fake([
            'api.tcgapi.dev/v1/prices/top-movers*' => Http::response(['data' => $cards]),
        ]);
    }

    private function game(): Game
    {
        return Game::factory()->create(['api_identifier' => 'pokemon']);
    }

    public function test_creates_ten_ranked_entries_for_the_game(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
        $game = $this->game();

        $cards = [];
        for ($i = 0; $i < 12; $i++) {
            $cards[] = [
                'card_id' => 1000 + $i,
                'name' => "Carte {$i}",
                'set_name' => 'Set test',
                'market_price' => 10 + $i,
                'price_change' => 5 + $i,
                'image_url' => null,
            ];
        }
        $this->fakeTopMovers($cards);

        (new FetchTrendingCardsJob($game))->handle(app(TCGApiService::class));

        $entries = DailyTrendingCard::where('game_id', $game->id)->whereDate('date', now()->toDateString())->get();

        $this->assertCount(10, $entries);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $entries->pluck('rank')->all());
        $this->assertSame(['Carte 0', 'Carte 9'], [$entries->first()->card->name, $entries->last()->card->name]);
        $this->assertSame('USD', $entries->first()->currency);
    }

    public function test_replaces_same_day_entries_on_second_run(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
        $game = $this->game();

        Http::fake([
            'api.tcgapi.dev/v1/prices/top-movers*' => Http::sequence()
                ->push(['data' => [
                    ['card_id' => 1, 'name' => 'Ancienne', 'set_name' => null, 'market_price' => 5, 'price_change' => 1],
                ]])
                ->push(['data' => [
                    ['card_id' => 1, 'name' => 'Ancienne', 'set_name' => null, 'market_price' => 5, 'price_change' => 1],
                    ['card_id' => 2, 'name' => 'Nouvelle', 'set_name' => null, 'market_price' => 8, 'price_change' => 2],
                ]]),
        ]);

        (new FetchTrendingCardsJob($game))->handle(app(TCGApiService::class));
        (new FetchTrendingCardsJob($game))->handle(app(TCGApiService::class));

        $count = DailyTrendingCard::where('game_id', $game->id)->whereDate('date', now()->toDateString())->count();

        $this->assertSame(2, $count);
        $this->assertSame(2, Card::where('game_id', $game->id)->count());
        $this->assertSame('Ancienne', Card::firstWhere('external_id', '1')->name);
    }

    public function test_skips_cards_without_market_price(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
        $game = $this->game();

        $this->fakeTopMovers([
            ['card_id' => 1, 'name' => 'Sans prix', 'set_name' => null, 'market_price' => null, 'price_change' => null],
            ['card_id' => 2, 'name' => 'Avec prix', 'set_name' => null, 'market_price' => 12.5, 'price_change' => 3],
        ]);
        (new FetchTrendingCardsJob($game))->handle(app(TCGApiService::class));

        $entries = DailyTrendingCard::where('game_id', $game->id)->get();

        $this->assertCount(1, $entries);
        $this->assertSame('Avec prix', $entries->first()->card->name);
        $this->assertSame(1, $entries->first()->rank);
    }
}
