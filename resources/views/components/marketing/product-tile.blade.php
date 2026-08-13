@props([
    'title',
    'description',
    'featured' => false,
])

@php
    $base = 'group relative flex flex-col overflow-hidden rounded-2xl border bg-white p-6 shadow-sm transition duration-300 dark:bg-slate-900/80';
    $border = $featured
        ? 'border-indigo-200 ring-1 ring-indigo-500/20 dark:border-indigo-500/30 dark:ring-indigo-400/10'
        : 'border-slate-200/90 dark:border-slate-800';
@endphp

<article {{ $attributes->merge(['class' => $base.' '.$border]) }}>
    @isset($icon)
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500/15 to-emerald-500/15 text-indigo-600 dark:from-indigo-400/20 dark:to-emerald-400/10 dark:text-indigo-300">
            {{ $icon }}
        </div>
    @endisset
    <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h3>
    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $description }}</p>
    <div class="pointer-events-none absolute inset-0 rounded-2xl opacity-0 ring-2 ring-indigo-500/0 transition group-hover:opacity-100 group-hover:ring-indigo-500/20 dark:group-hover:ring-indigo-400/15" aria-hidden="true"></div>
</article>
