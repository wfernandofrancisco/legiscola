@props([
    'title' => '',
    'divider' => true,
])

<div @class([
    'pt-5',
    'border-t border-gray-100 dark:border-gray-700' => $divider,
])>
    @if ($title)
        <h3 class="mb-4 text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
            {{ $title }}
        </h3>
    @endif
    {{ $slot }}
</div>
