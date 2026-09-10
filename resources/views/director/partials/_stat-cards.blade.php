@php
    /** @var array<int, array{label: string, value: int|string, hint?: string}> $stats */
@endphp

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
    @foreach ($stats as $stat)
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                {{ $stat['label'] }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">
                {{ number_format($stat['value'], 0, ',', '.') }}</p>
            @if (!empty($stat['hint']))
                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">{{ $stat['hint'] }}</p>
            @endif
        </div>
    @endforeach
</div>
