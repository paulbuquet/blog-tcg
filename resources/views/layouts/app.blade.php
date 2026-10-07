<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — Actualités TCG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-night-950 font-sans text-slate-200 antialiased">
    <header class="sticky top-0 z-40 border-b border-white/10 bg-night-950/80 backdrop-blur-lg">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 shadow-lg shadow-violet-500/25 transition group-hover:shadow-violet-500/40">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-white" />
                </span>
                <span class="font-display text-lg font-bold tracking-tight text-white">TCG <span class="bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">Blog</span></span>
            </a>
            <nav class="flex items-center gap-1">
                @foreach ($games ?? [] as $game)
                    @php $accent = $game->accent(); @endphp
                    <a href="{{ route('games.show', $game) }}"
                       class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition hover:bg-white/5 {{ request()->is('jeu/'.$game->slug) ? 'bg-white/5 text-white' : 'text-slate-400 hover:text-white' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $accent['dot'] }}"></span>
                        {{ $game->name }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-10">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-white/10 bg-night-900">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-500">
                        <x-heroicon-o-squares-2x2 class="h-4 w-4 text-white" />
                    </span>
                    <span class="font-display font-bold text-white">TCG Blog</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-slate-400">
                    Actualités et tendances de prix Pokémon, Magic: The Gathering et Yu-Gi-Oh!, générées chaque jour.
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Jeux suivis</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($games ?? [] as $game)
                        <li>
                            <a href="{{ route('games.show', $game) }}" class="text-slate-400 transition hover:text-white">{{ $game->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">À propos</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-400">
                    Les annonces de sorties sont rédigées par IA à partir des données officielles,
                    puis relues avant publication. Les prix proviennent de l'API tcgapi.dev.
                </p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto max-w-6xl px-4 py-5 text-center text-xs text-slate-500">
                {{ date('Y') }} TCG Blog — actualités et tendances Pokémon, Magic et Yu-Gi-Oh!
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
