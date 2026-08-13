@props([
    'color' => 'gray', // green, yellow, red, gray, blue, cyan, etc
    'text' => '',
    'showDot' => true,
])

@php
    $colorClasses = match ($color) {
        'green' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
        'red' => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
        'blue' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
        'cyan' => 'bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300',
        default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
    };

    $dotColor = match ($color) {
        'green' => 'bg-green-500',
        'yellow' => 'bg-yellow-500',
        'red' => 'bg-red-500',
        'blue' => 'bg-blue-500',
        'cyan' => 'bg-cyan-500',
        default => 'bg-gray-500',
    };
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold $colorClasses"]) }}>
    @if ($showDot)
        <span class="w-2 h-2 rounded-full  {{ $dotColor }}"></span>
    @endif
    &nbsp; {{ $text ?? $slot }}
</span>
