@extends('layouts.app')

@section('title', $game->name)

@php $accent = $game->accent(); @endphp

@section('content')
    <section class="relative mb-10 overflow-hidden rounded-3xl border border-white/10 bg-night-900">
        @if ($game->banner_path)
            <img src="{{ asset('storage/'.$game->banner_path) }}" alt="Bannière {{ $game->name }}" class="absolute inset-0 h-full w-full object-cover">
            <div class="pointer-events-none absolute inset-0 bg-night-950/70"></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-night-950/70 via-transparent to-night-950"></div>
        @else
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br {{ $accent['gradient'] }} opacity-15"></div>
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full {{ $accent['gradient'] }} opacity-25 blur-3xl"></div>
        @endif

        <div class="relative flex min-h-44 items-center gap-4 px-6 py-12 sm:px-10">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $accent['gradient'] }} shadow-lg">
                <x-heroicon-o-sparkles class="h-7 w-7 text-night-950" />
            </span>
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $game->name }}</h1>
                <p class="mt-1 text-sm text-slate-400">Annonces de sorties, banlists et tendances de prix.</p>
            </div>
        </div>
    </section>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            @forelse ($articles as $index => $article)
                <x-article-card :article="$article" :featured="$index === 0" />
            @empty
                <div class="rounded-2xl border border-dashed border-white/10 bg-night-900 p-12 text-center text-slate-400">
                    Aucun article publié pour {{ $game->name }} pour le moment.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $articles->links() }}
            </div>
        </div>

        <aside class="lg:sticky lg:top-20 lg:self-start">
            <livewire:trending-cards-widget :game="$game" :key="'trending-'.$game->slug" />
        </aside>
    </div>
@endsection
