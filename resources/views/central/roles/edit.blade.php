<x-layouts.central>
    <x-slot name="title">Editar Role</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('central.roles.includes._form', ['action' => 'edit', 'role' => $role])
    </div>
</x-layouts.central>
