@props([
    'model',
    'modelName' => 'Item',
    'modalName' => 'view-modal',
    'color' => 'cyan',
])

@php
    $uniqueId = "view-{$modalName}-{$model->id}";
@endphp

<button 
    type="button" 
    x-data=""
    class="inline-flex items-center cursor-pointer hover:bg-blue-100 hover:rounded-xl p-2"
    x-on:click.prevent="$dispatch('open-modal', '{{ $uniqueId }}')"
    title="Visualizar {{ $modelName }}"
>
    <svg class="w-6 h-6 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
        fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-width="2"
            d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
        <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
</button>
