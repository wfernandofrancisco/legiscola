<x-layouts.admin>
    <x-slot name="title">Editar Aula</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Aula" subtitle="Atualize os dados da aula." />
    @include('admin.class-lessons.includes._form', ['action' => 'edit', 'classLesson' => $classLesson])
</x-layouts.admin>
