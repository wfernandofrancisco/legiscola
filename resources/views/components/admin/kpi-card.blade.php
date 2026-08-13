@props([
    'label' => '',
    'value' => '',
    'tone' => 'indigo', // indigo|emerald|red|sky|amber|violet|gray|blue|pink
    'hint' => null,
])

@php
    $tones = [
        'indigo'  => ['border-indigo-200 dark:border-indigo-800',   'bg-indigo-50 dark:bg-indigo-950/40',   'text-indigo-700 dark:text-indigo-300',   'text-indigo-900 dark:text-indigo-100'],
        'emerald' => ['border-emerald-200 dark:border-emerald-800', 'bg-emerald-50 dark:bg-emerald-950/40', 'text-emerald-700 dark:text-emerald-300', 'text-emerald-900 dark:text-emerald-100'],
        'red'     => ['border-red-200 dark:border-red-800',         'bg-red-50 dark:bg-red-950/40',         'text-red-700 dark:text-red-300',         'text-red-900 dark:text-red-100'],
        'sky'     => ['border-sky-200 dark:border-sky-800',         'bg-sky-50 dark:bg-sky-950/40',         'text-sky-700 dark:text-sky-300',         'text-sky-900 dark:text-sky-100'],
        'amber'   => ['border-amber-200 dark:border-amber-800',     'bg-amber-50 dark:bg-amber-950/40',     'text-amber-700 dark:text-amber-300',     'text-amber-900 dark:text-amber-100'],
        'violet'  => ['border-violet-200 dark:border-violet-800',   'bg-violet-50 dark:bg-violet-950/40',   'text-violet-700 dark:text-violet-300',   'text-violet-900 dark:text-violet-100'],
        'blue'    => ['border-blue-200 dark:border-blue-800',       'bg-blue-50 dark:bg-blue-950/40',       'text-blue-700 dark:text-blue-300',       'text-blue-900 dark:text-blue-100'],
        'pink'    => ['border-pink-200 dark:border-pink-800',       'bg-pink-50 dark:bg-pink-950/40',       'text-pink-700 dark:text-pink-300',       'text-pink-900 dark:text-pink-100'],
        'gray'    => ['border-gray-200 dark:border-gray-700',       'bg-gray-50 dark:bg-gray-900/40',       'text-gray-600 dark:text-gray-300',       'text-gray-900 dark:text-gray-100'],
    ];
    [$border, $bg, $caption, $valueColor] = $tones[$tone] ?? $tones['indigo'];
@endphp

<div {{ $attributes->class(["rounded-xl border $border $bg p-4"]) }}>
    <p class="text-[11px] uppercase tracking-wide font-semibold {{ $caption }}">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold {{ $valueColor }} break-words">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
</div>
