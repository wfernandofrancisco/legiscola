@props([
    'items' => [], // [['label' => 'Home', 'href' => url('/')], ['label' => 'Current']]
])

@if (!empty($items))
    <nav class="flex items-center gap-2 text-sm mb-4">
        @foreach ($items as $index => $item)
            @if ($loop->last)
                <span class="text-gray-600 dark:text-gray-400">{{ $item['label'] }}</span>
            @else
                <a href="{{ $item['href'] ?? '#' }}"
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium transition">
                    {{ $item['label'] }}
                </a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            @endif
        @endforeach
    </nav>
@endif
