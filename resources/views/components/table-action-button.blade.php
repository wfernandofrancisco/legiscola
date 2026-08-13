@props([
    'color' => 'gray', // cyan, blue, red, green, yellow
    'icon' => null,
    'title' => '',
    'onclick' => null,
    'href' => null,
    'type' => 'button', // button, link — quando não é link, use submit=true para enviar form
    'submit' => false,
])

@php
    $styles = [
        'cyan' =>
            'text-cyan-500 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 hover:bg-cyan-100 dark:hover:bg-cyan-950',
        'blue' =>
            'text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-950',
        'red' =>
            'text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-950',
        'green' =>
            'text-green-500 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 hover:bg-green-100 dark:hover:bg-green-950',
        'yellow' =>
            'text-yellow-500 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-950',
        'gray' =>
            'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-950',
    ];
    $baseClass = $styles[$color] ?? $styles['gray'];
@endphp

@if ($type === 'link')
    <a href="{{ $href }}" title="{{ $title }}"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition {{ $baseClass }}">
        {{ $slot }}
    </a>
@else
    <button type="{{ $submit ? 'submit' : 'button' }}" title="{{ $title }}"
        @if ($onclick) onclick="{{ $onclick }}" @endif
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition cursor-pointer {{ $baseClass }}">
        {{ $slot }}
    </button>
@endif
