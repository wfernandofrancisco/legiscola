@props(['value', 'label'])

<div class="text-center sm:text-left">
    <p class="text-3xl font-bold tracking-tight text-slate-900 tabular-nums dark:text-white sm:text-4xl">{{ $value }}</p>
    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
</div>
