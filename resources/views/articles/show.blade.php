@extends('layouts.app')

@section('title', $article->title)

@php
    $accent = $article->game?->accent() ?? \App\Models\Game::defaultAccent();
    $readingTime = max(1, (int) ceil(str_word_count(strip_tags($article->content)) / 200));
@endphp

@section('content')
    <article class="mx-auto max-w-3xl">
        <div class="relative mb-8 overflow-hidden rounded-3xl border border-white/10 bg-night-900 px-6 py-10 sm:px-10">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br {{ $accent['gradient'] }} opacity-10"></div>
            <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full {{ $accent['gradient'] }} opacity-20 blur-3xl"></div>

            <div class="relative">
                <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
                    @if ($article->game)
                        <span class="rounded-full px-2.5 py-1 font-semibold ring-1 ring-inset {{ $accent['badge'] }}">{{ $article->game->name }}</span>
                    @endif
                    <span class="rounded-full bg-white/5 px-2.5 py-1 font-medium text-slate-300 ring-1 ring-inset ring-white/10">{{ $article->type->label() }}</span>
                    <time datetime="{{ $article->published_at?->toIso8601String() }}" class="text-slate-400">
                        {{ $article->published_at?->format('d/m/Y') }}
                    </time>
                    <span class="text-slate-500">·</span>
                    <span class="text-slate-400">{{ $readingTime }} min de lecture</span>
                </div>

                <h1 class="font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $article->title }}</h1>
            </div>
        </div>

        <div class="prose prose-invert prose-lg max-w-none prose-headings:font-display prose-headings:tracking-tight prose-a:text-violet-400 prose-a:no-underline hover:prose-a:underline prose-strong:text-white prose-blockquote:border-l-violet-500 prose-blockquote:text-slate-300 prose-code:text-violet-300 prose-pre:bg-night-900 prose-pre:border prose-pre:border-white/10">
            {!! \App\Support\Markdown::toHtml($article->content) !!}
        </div>

        <div class="mt-12 flex items-center justify-between border-t border-white/10 pt-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-violet-400 transition hover:text-violet-300">
                <x-heroicon-m-arrow-long-left class="h-4 w-4" />
                Retour aux actualités
            </a>
            @if ($article->game)
                <a href="{{ route('games.show', $article->game) }}" class="text-sm font-medium text-slate-400 transition hover:text-white">
                    Toutes les news {{ $article->game->name }} →
                </a>
            @endif
        </div>
    </article>
@endsection
