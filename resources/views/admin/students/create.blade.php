<x-layouts.admin>
    <x-slot name="title">Novo Aluno</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Aluno" subtitle="Cadastre os dados do estudante." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.students.includes._form', ['action' => 'create', 'student' => null])
    </div>
</x-layouts.admin>
