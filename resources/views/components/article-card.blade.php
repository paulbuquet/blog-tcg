@props(['article', 'featured' => false])

@php
    $accent = $article->game?->accent() ?? \App\Models\Game::defaultAccent();
    $readingTime = max(1, (int) ceil(str_word_count(strip_tags($article->content)) / 200));
@endphp

<article @class([
    'group relative flex flex-col overflow-hidden rounded-2xl border border-white/10 bg-night-900 transition duration-300 hover:-translate-y-1 hover:border-white/20 hover:shadow-[0_12px_40px_-12px_rgba(139,92,246,0.4)]',
    'lg:flex-row lg:items-stretch' => $featured,
])>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"></div>

    <div @class([
        'flex flex-1 flex-col',
        'p-6' => ! $featured,
        'p-8 lg:justify-center lg:p-10' => $featured,
    ])>
        <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
            @if ($article->game)
                <span class="rounded-full px-2.5 py-1 font-semibold ring-1 ring-inset {{ $accent['badge'] }}">{{ $article->game->name }}</span>
            @endif
            <span class="rounded-full bg-white/5 px-2.5 py-1 font-medium text-slate-400 ring-1 ring-inset ring-white/10">{{ $article->type->label() }}</span>
            <time datetime="{{ $article->published_at?->toIso8601String() }}" class="text-slate-500">
                {{ $article->published_at?->format('d/m/Y') }}
            </time>
        </div>

        <h2 @class(['font-display font-bold tracking-tight text-white', 'text-xl' => ! $featured, 'text-2xl sm:text-3xl' => $featured])>
            <a href="{{ route('articles.show', $article) }}" class="transition hover:text-violet-300">{{ $article->title }}</a>
        </h2>

        <p @class([
            'mt-3 line-clamp-3 text-sm leading-relaxed text-slate-400',
            'lg:max-w-3xl' => $featured,
        ])>
            {{ Str::limit(strip_tags(\App\Support\Markdown::toHtml($article->content)), 260) }}
        </p>

        <div class="mt-auto flex items-center justify-between pt-5 text-xs text-slate-500">
            <span>{{ $readingTime }} min de lecture</span>
            <span class="inline-flex translate-x-1 items-center gap-1 font-semibold text-violet-400 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100">
                Lire l'article
                <x-heroicon-m-arrow-long-right class="h-4 w-4" />
            </span>
        </div>
    </div>
</article>
