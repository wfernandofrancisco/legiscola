<x-layouts.admin>
    <x-slot name="title">Novo Usuário</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-subpage-header title="Cadastrar Usuário" subtitle="Cadastre as informações do usuário." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.users.includes._form', [
            'action' => 'create',
            'user' => null,
            'userTypes' => $userTypes ?? null,
            'roles' => $roles ?? null,
            'statuses' => $statuses ?? null
        ])
    </div>
</x-layouts.admin>
