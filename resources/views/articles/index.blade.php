@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
    <section class="relative mb-12 overflow-hidden rounded-3xl border border-white/10 bg-night-900 px-6 py-12 sm:px-10 sm:py-16">
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-violet-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-fuchsia-600/15 blur-3xl"></div>

        <div class="relative">
            <span class="inline-flex items-center gap-2 rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-300">
                <x-heroicon-m-sparkles class="h-3.5 w-3.5" />
                Articles rédigés par IA, relus avant publication
            </span>
            <h1 class="mt-5 max-w-2xl font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">
                Les dernières actualités<br>
                <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-amber-300 bg-clip-text text-transparent">TCG</span>, chaque jour.
            </h1>
            <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-400">
                Annonces de sorties, banlists et tendances de prix pour Pokémon, Magic: The Gathering et Yu-Gi-Oh!.
            </p>

            <div class="mt-7 flex flex-wrap gap-3">
                @foreach ($games as $game)
                    @php $accent = $game->accent(); @endphp
                    <a href="{{ route('games.show', $game) }}"
                       class="group flex items-center gap-2.5 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 transition hover:border-white/25 hover:bg-white/10">
                        <span class="h-2.5 w-2.5 rounded-full {{ $accent['dot'] }}"></span>
                        <span class="text-sm font-semibold text-white">{{ $game->name }}</span>
                        <x-heroicon-m-chevron-right class="h-4 w-4 text-slate-500 transition group-hover:translate-x-0.5 group-hover:text-white" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            @forelse ($articles as $index => $article)
                <x-article-card :article="$article" :featured="$index === 0" />
            @empty
                <div class="rounded-2xl border border-dashed border-white/10 bg-night-900 p-12 text-center text-slate-400">
                    Aucun article publié pour le moment.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $articles->links() }}
            </div>
        </div>

        <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
            @foreach ($games as $game)
                <livewire:trending-cards-widget :game="$game" :key="'trending-'.$game->slug" />
            @endforeach
        </aside>
    </div>
@endsection
