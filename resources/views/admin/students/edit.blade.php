<x-layouts.admin>
    <x-slot name="title">Editar Aluno</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Aluno" subtitle="Atualize os dados do estudante." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.students.includes._form', ['action' => 'edit', 'student' => $student])
    </div>
</x-layouts.admin>
