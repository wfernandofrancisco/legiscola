<x-layouts.admin>
    <x-slot name="title">Editar Curso</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Curso" subtitle="Atualize as informações do curso." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.courses.includes._form', ['action' => 'edit', 'course' => $course])
    </div>
</x-layouts.admin>
