<x-layouts.central>
    <x-slot name="title">Novo Tenant</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('central.tenants.includes._form', ['action' => 'create', 'tenant' => null])
    </div>
</x-layouts.central>
