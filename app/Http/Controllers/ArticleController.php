<?php

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Game;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::query()
            ->with('game')
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('articles.index', [
            'articles' => $articles,
            'games' => Game::active()->orderBy('name')->get(),
        ]);
    }

    public function show(Article $article)
    {
        abort_unless($article->status === ArticleStatus::Published, 404);

        return view('articles.show', [
            'article' => $article,
            'games' => Game::active()->orderBy('name')->get(),
        ]);
    }

    public function game(Game $game)
    {
        $articles = $game->articles()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('games.show', [
            'game' => $game,
            'articles' => $articles,
            'games' => Game::active()->orderBy('name')->get(),
        ]);
    }
}
