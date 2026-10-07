<?php

namespace App\Livewire;

use App\Models\DailyTrendingCard;
use App\Models\Game;
use Livewire\Component;

class TrendingCardsWidget extends Component
{
    public Game $game;

    public function render()
    {
        $entries = DailyTrendingCard::query()
            ->with('card')
            ->where('game_id', $this->game->id)
            ->whereDate('date', now()->toDateString())
            ->orderBy('rank')
            ->get();

        return view('livewire.trending-cards-widget', [
            'entries' => $entries,
            'lastSync' => $this->lastSyncDate(),
        ]);
    }

    private function lastSyncDate(): ?string
    {
        return DailyTrendingCard::query()
            ->where('game_id', $this->game->id)
            ->max('date');
    }
}
