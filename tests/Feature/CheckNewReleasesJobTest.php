<?php

namespace Tests\Feature;

use App\Jobs\CheckNewReleasesJob;
use App\Jobs\GenerateReleaseArticleJob;
use App\Models\Game;
use App\Models\SetRelease;
use App\Services\TCGApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckNewReleasesJobTest extends TestCase
{
    use RefreshDatabase;

    private function game(): Game
    {
        return Game::factory()->create(['api_identifier' => 'pokemon']);
    }

    private function fakeSets(array $sets): void
    {
        Http::fake([
            'api.tcgapi.dev/v1/sets*' => Http::response([
                'data' => $sets,
                'meta' => ['has_more' => false],
            ]),
        ]);
    }

    public function test_first_import_inserts_sets_without_generating_articles(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
        Queue::fake();
        $game = $this->game();

        $this->fakeSets([
            ['id' => 11, 'name' => 'Set A', 'release_date' => '2026-12-01'],
            ['id' => 12, 'name' => 'Set B', 'release_date' => null],
        ]);

        (new CheckNewReleasesJob($game))->handle(app(TCGApiService::class));

        $this->assertSame(2, SetRelease::where('game_id', $game->id)->count());
        $this->assertSame('2026-12-01', SetRelease::firstWhere('external_id', '11')->release_date->toDateString());
        Queue::assertNothingPushed();
    }

    public function test_second_run_detects_new_set_and_dispatches_article_generation(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
        Queue::fake();
        $game = $this->game();

        Http::fake([
            'api.tcgapi.dev/v1/sets*' => Http::sequence()
                ->push(['data' => [
                    ['id' => 11, 'name' => 'Set A', 'release_date' => '2026-12-01'],
                ], 'meta' => ['has_more' => false]])
                ->push(['data' => [
                    ['id' => 11, 'name' => 'Set A', 'release_date' => '2026-12-01'],
                    ['id' => 13, 'name' => 'Set C', 'release_date' => '2027-03-15'],
                ], 'meta' => ['has_more' => false]]),
        ]);

        (new CheckNewReleasesJob($game))->handle(app(TCGApiService::class));
        (new CheckNewReleasesJob($game))->handle(app(TCGApiService::class));

        $newSet = SetRelease::firstWhere('external_id', '13');

        $this->assertSame(2, SetRelease::where('game_id', $game->id)->count());
        $this->assertSame(now()->toDateString(), $newSet->announced_at->toDateString());
        Queue::assertPushed(GenerateReleaseArticleJob::class, fn ($job) => $job->setRelease->id === $newSet->id);
    }

    public function test_known_sets_are_not_duplicated(): void
    {
        config()->set('services.tcg_api.key', 'test-key');
        Queue::fake();
        $game = $this->game();

        SetRelease::factory()->create(['game_id' => $game->id, 'external_id' => '11']);

        $this->fakeSets([
            ['id' => 11, 'name' => 'Set A', 'release_date' => '2026-12-01'],
        ]);
        (new CheckNewReleasesJob($game))->handle(app(TCGApiService::class));

        $this->assertSame(1, SetRelease::where('game_id', $game->id)->count());
        Queue::assertNothingPushed();
    }
}
