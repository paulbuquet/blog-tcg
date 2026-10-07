<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_article_resource_list_renders_for_admin(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        Article::factory()->create(['game_id' => $game->id, 'title' => 'Brouillon test']);

        $this->actingAs($user)
            ->get('/admin/articles')
            ->assertOk()
            ->assertSee('Brouillon test');
    }

    public function test_article_resource_form_pages_render_for_admin(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $article = Article::factory()->create(['game_id' => $game->id]);

        $this->actingAs($user)
            ->get('/admin/articles/create')
            ->assertOk();

        $this->actingAs($user)
            ->get("/admin/articles/{$article->id}/edit")
            ->assertOk();
    }

    public function test_game_resource_edit_page_renders_for_admin(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        $this->actingAs($user)
            ->get("/admin/games/{$game->id}/edit")
            ->assertOk();
    }
}
