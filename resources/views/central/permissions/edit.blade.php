<x-layouts.central>
    <x-slot name="title">Editar Permissão</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    <div class="max-w-3xl bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('central.permissions.includes._form', ['action' => 'edit', 'permission' => $permission])
    </div>
</x-layouts.central>
