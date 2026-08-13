<x-layouts.admin>
    <x-slot name="title">Novo Curso</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Curso" subtitle="Cadastre as informações do curso." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.courses.includes._form', ['action' => 'create', 'course' => null])
    </div>
</x-layouts.admin>
