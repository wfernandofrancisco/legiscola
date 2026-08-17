@props([
    'caption' => null,
    'url' => null,
    /** Caminho relativo a public/ para substituir a ilustração por um print real. */
    'image' => null,
    'alt' => null,
])

@php
    $hasImage = filled($image) && file_exists(public_path($image));
    $addressBar = $url ? request()->getHost().$url : null;
@endphp

<figure {{ $attributes->class(['overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40']) }}>
    <div class="flex items-center gap-3 border-b border-slate-200 bg-slate-100/90 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/80">
        <div class="flex shrink-0 gap-1.5" aria-hidden="true">
            <span class="h-2.5 w-2.5 rounded-full bg-red-400/90"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-amber-400/90"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/90"></span>
        </div>
        @if ($addressBar)
            <span class="flex min-w-0 flex-1 items-center gap-1.5 rounded-full bg-white px-3 py-1 text-[10px] font-medium text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:ring-slate-700">
                <svg class="h-2.5 w-2.5 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V8H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 7V5.5a3 3 0 1 0-6 0V8h6Z" clip-rule="evenodd"/>
                </svg>
                <span class="truncate">{{ $addressBar }}</span>
            </span>
        @endif
    </div>

    <div class="bg-gradient-to-b from-white to-slate-50 p-4 sm:p-5 dark:from-slate-900 dark:to-slate-950">
        @if ($hasImage)
            <img src="{{ asset($image) }}" alt="{{ $alt ?? $caption }}" loading="lazy"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700"/>
        @else
            {{ $slot }}
        @endif
    </div>

    @if (filled($caption))
        <figcaption class="border-t border-slate-200 bg-white px-4 py-2.5 text-[11px] leading-relaxed text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
            {{ $caption }}
        </figcaption>
    @endif
</figure>
