<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\DailyTrendingCard;
use App\Models\SetRelease;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $lastSync = DailyTrendingCard::query()->max('date');

        return [
            Stat::make('Brouillons en attente', Article::query()->where('status', ArticleStatus::Draft)->count())
                ->description('À relire et valider dans Filament')
                ->color('warning'),
            Stat::make('Articles publiés', Article::query()->where('status', ArticleStatus::Published)->count())
                ->color('success'),
            Stat::make('Sets sans article généré', SetRelease::query()->whereNull('processed_at')->count())
                ->description('En attente du job GenerateReleaseArticle')
                ->color('info'),
            Stat::make('Dernière synchro tendances', $lastSync ? Carbon::parse($lastSync)->format('d/m/Y') : 'Jamais')
                ->description('Widget Top 10 du jour')
                ->color('gray'),
        ];
    }
}
