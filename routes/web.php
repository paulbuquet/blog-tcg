<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::get('/article/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/jeu/{game:slug}', [ArticleController::class, 'game'])->name('games.show');
