<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\SetRelease;
use App\Services\TCGApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckNewReleasesJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public Game $game) {}

    public function handle(TCGApiService $api): void
    {
        $hadExistingReleases = $this->game->setReleases()->exists();

        foreach ($api->sets($this->game->api_identifier) as $set) {
            $externalId = (string) ($set['id'] ?? $set['slug'] ?? '');
            if ($externalId === '') {
                continue;
            }

            $setRelease = SetRelease::firstOrCreate(
                ['game_id' => $this->game->id, 'external_id' => $externalId],
                [
                    'name' => $set['name'] ?? 'Set inconnu',
                    'release_date' => $set['release_date'] ?? null,
                    'announced_at' => now()->toDateString(),
                ],
            );

            $firstImport = ! $hadExistingReleases;
            if ($setRelease->wasRecentlyCreated && ! $firstImport) {
                GenerateReleaseArticleJob::dispatch($setRelease);
            }
        }
    }
}
