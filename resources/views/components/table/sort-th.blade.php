@props([
    'column',
    'label',
    'align' => 'left',
])

@php
    $sortBy  = request('sort_by');
    $sortDir = request('sort_dir', 'asc');
    $isActive = $sortBy === $column;
    $nextDir  = ($isActive && $sortDir === 'asc') ? 'desc' : 'asc';

    // Preserva todos os filtros atuais, troca apenas sort e reseta paginação
    $url = request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $nextDir, 'page' => null]);

    $alignClass = match($align) {
        'center' => 'text-center',
        'right'  => 'text-right',
        default  => 'text-left',
    };
@endphp

<th {{ $attributes->class(["px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 $alignClass"]) }}>
    <a href="{{ $url }}"
       class="inline-flex items-center gap-1 {{ $align === 'center' ? 'justify-center w-full' : '' }} hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors group whitespace-nowrap">
        {{ $label }}
        <span class="{{ $isActive ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-300 dark:text-gray-600 group-hover:text-gray-400 dark:group-hover:text-gray-500' }}">
            @if($isActive && $sortDir === 'asc')
                {{-- Seta para cima --}}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
            @elseif($isActive && $sortDir === 'desc')
                {{-- Seta para baixo --}}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            @else
                {{-- Ícone neutro (duplo) --}}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
            @endif
        </span>
    </a>
</th>
