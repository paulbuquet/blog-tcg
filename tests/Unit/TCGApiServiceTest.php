<?php

namespace Tests\Unit;

use App\Services\TCGApiService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TCGApiServiceTest extends TestCase
{
    private function withKey(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
    }

    public function test_top_movers_maps_response_data(): void
    {
        $this->withKey();

        Http::fake([
            'api.tcgapi.dev/v1/prices/top-movers*' => Http::response([
                'data' => [
                    [
                        'card_id' => 12345,
                        'name' => 'Charizard ex',
                        'set_name' => 'Obsidian Flames',
                        'market_price' => 24.99,
                        'price_change' => 15.5,
                        'image_url' => 'https://example.com/charizard.png',
                    ],
                ],
            ]),
        ]);

        $result = app(TCGApiService::class)->topMovers('pokemon');

        $this->assertCount(1, $result);
        $this->assertSame(12345, $result[0]['card_id']);
        $this->assertSame('Charizard ex', $result[0]['name']);
        $this->assertSame(15.5, $result[0]['price_change']);
    }

    public function test_top_movers_sends_expected_query_params(): void
    {
        $this->withKey();

        Http::fake([
            'api.tcgapi.dev/v1/prices/top-movers*' => Http::response(['data' => []]),
        ]);

        Http::assertNothingSent();
        app(TCGApiService::class)->topMovers('yugioh', 'down', '7d', 25);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'game=yugioh')
                && str_contains($request->url(), 'direction=down')
                && str_contains($request->url(), 'period=7d')
                && str_contains($request->url(), 'limit=25')
                && $request->hasHeader('X-API-Key', 'test-key');
        });
    }

    public function test_sets_paginates_through_all_pages(): void
    {
        $this->withKey();

        Http::fake([
            'api.tcgapi.dev/v1/sets*' => function ($request) {
                $page = $request->data()['page'] ?? 1;

                return Http::response([
                    'data' => [
                        ['id' => $page * 10 + 1, 'name' => "Set page {$page}", 'release_date' => '2026-01-01'],
                        ['id' => $page * 10 + 2, 'name' => "Set page {$page} bis"],
                    ],
                    'meta' => ['has_more' => $page < 2],
                ]);
            },
        ]);

        $result = app(TCGApiService::class)->sets('pokemon');

        $this->assertCount(4, $result);
        $this->assertSame('Set page 2 bis', $result[3]['name']);
    }

    public function test_throws_without_api_key(): void
    {
        config()->set('services.tcg_api.key', null);

        $this->expectException(RuntimeException::class);

        app(TCGApiService::class)->topMovers('pokemon');
    }
}
