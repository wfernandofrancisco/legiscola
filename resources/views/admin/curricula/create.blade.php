<x-layouts.admin>
    <x-slot name="title">Nova Disciplina</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Disciplina" subtitle="Cadastre um item da grade curricular." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.curricula.includes._form', ['action' => 'create', 'curriculum' => null, 'courses' => $courses])
    </div>
</x-layouts.admin>
