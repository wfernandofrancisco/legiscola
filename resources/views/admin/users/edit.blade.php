<x-layouts.admin>
    <x-slot name="title">Editar Usuário</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-subpage-header title="Editar Usuário" subtitle="Atualize as informações do usuário." />

     <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.users.includes._form', [
            'action' => 'edit',
            'user' => $user,
            'userTypes' => $userTypes ?? null,
            'roles' => $roles ?? null,
            'statuses' => $statuses ?? null
        ])
    </div>
</x-layouts.admin>
