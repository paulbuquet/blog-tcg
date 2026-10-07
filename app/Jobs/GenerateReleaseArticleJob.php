<?php

namespace App\Jobs;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\SetRelease;
use App\Services\OpenAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateReleaseArticleJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public function __construct(public SetRelease $setRelease) {}

    public function handle(OpenAIService $openAI): void
    {
        if ($this->setRelease->articles()->exists()) {
            return;
        }

        $content = $openAI->generateReleaseArticle([
            'name' => $this->setRelease->name,
            'game_name' => $this->setRelease->game->name,
            'release_date' => $this->setRelease->release_date?->format('d/m/Y') ?? 'date non précisée',
        ]);

        Article::create([
            'game_id' => $this->setRelease->game_id,
            'set_release_id' => $this->setRelease->id,
            'type' => ArticleType::ReleaseAnnouncement,
            'title' => "{$this->setRelease->name} : la sortie officielle est annoncée",
            'content' => $content,
            'status' => ArticleStatus::Draft,
        ]);

        $this->setRelease->update(['processed_at' => now()]);
    }
}
