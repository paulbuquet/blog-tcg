@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <div class="text-sm text-slate-500">
            {!! __('Affichage de') !!}
            @if ($paginator->firstItem())
                <span class="font-semibold text-slate-300">{{ $paginator->firstItem() }}</span>
                {!! __('à') !!}
                <span class="font-semibold text-slate-300">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('sur') !!}
            <span class="font-semibold text-slate-300">{{ $paginator->total() }}</span>
            {!! __('articles') !!}
        </div>

        <div class="flex items-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-white/5 bg-white/5 text-slate-600">
                    <x-heroicon-m-chevron-left class="h-4 w-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-slate-400 transition hover:border-violet-500/40 hover:text-white">
                    <x-heroicon-m-chevron-left class="h-4 w-4" />
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="px-2 text-sm text-slate-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-500 text-sm font-bold text-white shadow-lg shadow-violet-500/25">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Aller à la page :page', ['page' => $page]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-sm font-medium text-slate-400 transition hover:border-violet-500/40 hover:text-white">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-slate-400 transition hover:border-violet-500/40 hover:text-white">
                    <x-heroicon-m-chevron-right class="h-4 w-4" />
                </a>
            @else
                <span aria-disabled="true" class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-white/5 bg-white/5 text-slate-600">
                    <x-heroicon-m-chevron-right class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
