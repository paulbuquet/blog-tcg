<?php

use App\Jobs\CheckNewReleasesJob;
use App\Jobs\FetchTrendingCardsJob;
use App\Models\Game;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Game::query()->where('is_active', true)->each(
        fn (Game $game) => FetchTrendingCardsJob::dispatch($game),
    );
})->name('fetch-trending-cards')->dailyAt('07:00')->withoutOverlapping();

Schedule::call(function () {
    Game::query()->where('is_active', true)->each(
        fn (Game $game) => CheckNewReleasesJob::dispatch($game),
    );
})->name('check-new-releases')->dailyAt('07:15')->withoutOverlapping();
