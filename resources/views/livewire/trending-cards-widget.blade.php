@php $accent = $game->accent(); @endphp

<div class="relative overflow-hidden rounded-2xl border border-white/10 bg-night-900 transition hover:border-white/20">
    <div class="pointer-events-none absolute -right-12 -top-12 h-32 w-32 rounded-full {{ $accent['gradient'] }} opacity-15 blur-2xl"></div>

    <div class="relative border-b border-white/10 px-5 py-4">
        <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full {{ $accent['dot'] }}"></span>
            <h2 class="font-display text-sm font-bold text-white">Top 10 tendances</h2>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            @if ($entries->isEmpty())
                Données indisponibles pour aujourd'hui.
            @else
                {{ $game->name }} — hausses de prix sur 24h
            @endif
        </p>
    </div>

    @if ($entries->isEmpty())
        <div class="relative px-5 py-8 text-center text-sm text-slate-500">
            Le classement sera disponible après la première exécution du job quotidien.
        </div>
    @else
        <ol class="relative divide-y divide-white/5">
            @foreach ($entries as $entry)
                <li class="group flex items-center gap-3 px-5 py-3 transition hover:bg-white/5">
                    <span @class([
                        'w-7 shrink-0 text-center text-sm font-bold',
                        'bg-gradient-to-b from-amber-300 to-amber-500 bg-clip-text text-transparent' => $entry->rank <= 3,
                        'text-slate-500' => $entry->rank > 3,
                    ])>
                        {{ $entry->rank }}
                    </span>
                    @if ($entry->card->image_url)
                        <img src="{{ $entry->card->image_url }}" alt="{{ $entry->card->name }}" class="h-14 w-10 rounded-lg object-cover shadow-md ring-1 ring-white/10" loading="lazy">
                    @else
                        <span class="flex h-14 w-10 items-center justify-center rounded-lg bg-white/5 text-slate-600 ring-1 ring-white/10">
                            <x-heroicon-o-photo class="h-4 w-4" />
                        </span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ $entry->card->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $entry->card->set_name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">{{ number_format((float) $entry->price, 2) }} $</p>
                        <p class="text-xs font-semibold {{ $entry->price_change_percent >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $entry->price_change_percent >= 0 ? '▲ +' : '▼ ' }}{{ number_format(abs((float) $entry->price_change_percent), 1) }}%
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
        <div class="border-t border-white/10 px-5 py-3">
            <p class="text-[11px] text-slate-600">Source : tcgapi.dev — mise à jour quotidienne à 07:00.</p>
        </div>
    @endif
</div>
