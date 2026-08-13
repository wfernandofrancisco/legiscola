@props([
    'name' => 'view-modal',
    'title' => 'Détalhes',
    'modelName' => 'Item',
])

<x-modal :name="$name" :show="false">
    <div class="p-6 max-w-2xl">
        {{ $slot }}
        
        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Fechar') }}
            </x-secondary-button>
        </div>
    </div>
</x-modal>
