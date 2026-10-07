<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_lists_only_published_articles(): void
    {
        $game = Game::factory()->create();

        Article::factory()->published()->create(['game_id' => $game->id, 'title' => 'Article publié']);
        Article::factory()->create(['game_id' => $game->id, 'title' => 'Brouillon secret']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Article publié');
        $response->assertDontSee('Brouillon secret');
    }

    public function test_article_page_renders_published_article_with_markdown(): void
    {
        $game = Game::factory()->create(['name' => 'Pokémon']);
        $article = Article::factory()->published()->create([
            'game_id' => $game->id,
            'title' => 'Titre test',
            'content' => "# Grand titre\n\nUn **paragraphe** avec du markdown.",
        ]);

        $response = $this->get(route('articles.show', $article));

        $response->assertOk();
        $response->assertSee('<h1>Grand titre</h1>', false);
        $response->assertSee('<strong>paragraphe</strong>', false);
        $response->assertSee('Pokémon');
    }

    public function test_draft_article_returns_404(): void
    {
        $game = Game::factory()->create();
        $article = Article::factory()->create(['game_id' => $game->id]);

        $response = $this->get(route('articles.show', $article));

        $response->assertNotFound();
    }

    public function test_game_page_shows_articles_and_trending_widget(): void
    {
        $game = Game::factory()->create(['name' => 'Yu-Gi-Oh!', 'slug' => 'yugioh']);
        Article::factory()->published()->create(['game_id' => $game->id, 'title' => 'Article YGO']);

        $response = $this->get(route('games.show', $game));

        $response->assertOk();
        $response->assertSee('Article YGO');
        $response->assertSee('Top 10 tendances');
    }

    public function test_inactive_games_are_not_shown_in_navigation(): void
    {
        $active = Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon', 'is_active' => true]);
        $inactive = Game::factory()->create(['name' => 'One Piece', 'slug' => 'one-piece', 'is_active' => false]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('pokemon', false);
        $response->assertDontSee('one-piece', false);
    }
}
