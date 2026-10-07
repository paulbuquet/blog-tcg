<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Jobs\GenerateReleaseArticleJob;
use App\Models\Article;
use App\Models\Game;
use App\Models\SetRelease;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class GenerateReleaseArticleJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('openai.api_key', 'test-key');
        config()->set('openai.model', 'gpt-4o-mini');
    }

    private function fakeOpenAI(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => "## Annonce officielle\n\nLe set **Obsidian Flames** a été officiellement annoncé par la société.",
                        ],
                    ],
                ],
            ]),
        ]);
    }

    private function setRelease(): SetRelease
    {
        $game = Game::factory()->create(['name' => 'Pokémon']);

        return SetRelease::factory()->create([
            'game_id' => $game->id,
            'name' => 'Obsidian Flames',
            'release_date' => '2026-12-01',
            'processed_at' => null,
        ]);
    }

    public function test_creates_draft_release_announcement_article(): void
    {
        $this->fakeOpenAI();
        $setRelease = $this->setRelease();

        (new GenerateReleaseArticleJob($setRelease))->handle(app(OpenAIService::class));

        $article = $setRelease->articles()->first();

        $this->assertNotNull($article);
        $this->assertSame(ArticleType::ReleaseAnnouncement, $article->type);
        $this->assertSame(ArticleStatus::Draft, $article->status);
        $this->assertSame('Obsidian Flames : la sortie officielle est annoncée', $article->title);
        $this->assertStringContainsString('officiellement annoncé', $article->content);
        $this->assertNotNull($setRelease->fresh()->processed_at);
        $this->assertNotNull($article->slug);
    }

    public function test_is_idempotent_when_article_already_exists(): void
    {
        $this->fakeOpenAI();
        $setRelease = $this->setRelease();

        (new GenerateReleaseArticleJob($setRelease))->handle(app(OpenAIService::class));
        (new GenerateReleaseArticleJob($setRelease->fresh()))->handle(app(OpenAIService::class));

        $this->assertSame(1, Article::where('set_release_id', $setRelease->id)->count());
    }

    public function test_prompt_includes_set_and_game_context(): void
    {
        $this->fakeOpenAI();
        $setRelease = $this->setRelease();

        (new GenerateReleaseArticleJob($setRelease))->handle(app(OpenAIService::class));

        OpenAI::assertSent(Chat::class);

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $prompt = $parameters['messages'][1]['content'] ?? '';

            return $method === 'create'
                && str_contains($prompt, 'Obsidian Flames')
                && str_contains($prompt, 'Pokémon');
        });
    }
}
