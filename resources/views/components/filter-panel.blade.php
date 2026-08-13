@props([
    'title' => 'Pesquisa e filtros',
    'subtitle' => null,
    'resetHref' => null,
    'resetText' => 'Limpar filtros',
])

<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                {{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($resetHref)
            <a href="{{ $resetHref }}"
                class="inline-flex items-center gap-2 self-start rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ $resetText }}
            </a>
        @endif
    </div>

    {{ $slot }}
</div>
