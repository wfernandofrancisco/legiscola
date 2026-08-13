@props(['title', 'subtitle' => null, 'actionHref' => null, 'actionText' => null, 'items' => []])

@php
    $colorMap = [
        'emerald' => 'bg-emerald-400',
        'sky' => 'bg-sky-400',
        'blue' => 'bg-blue-400',
        'amber' => 'bg-amber-400',
        'rose' => 'bg-rose-400',
        'violet' => 'bg-violet-400',
        'gray' => 'bg-gray-400',
    ];
@endphp

<section
    class="relative mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-blue-800 to-indigo-600 px-5 py-4 shadow-lg dark:border-slate-700 dark:from-blue-900 dark:to-indigo-900">
    <div class="absolute inset-0 opacity-10">
        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="page-header-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M 32 0 L 0 0 0 32" fill="none" stroke="white" stroke-width="1"></path>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#page-header-grid)"></rect>
        </svg>
    </div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold tracking-tight text-white">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-0.5 text-sm text-white/85">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @if (!empty($items))
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-white/90">
                    @foreach ($items as $item)
                        @php
                            $dotClass = $colorMap[$item['color'] ?? 'gray'] ?? $colorMap['gray'];
                        @endphp
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 backdrop-blur-sm">
                            <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                            {{ $item['value'] ?? null }} {{ $item['title'] ?? null }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($actionHref && $actionText)
            <a href="{{ $actionHref }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-blue-800 shadow-sm transition hover:bg-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $actionText }}
            </a>
        @endif
    </div>
</section>
