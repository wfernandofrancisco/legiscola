<x-layouts.professor>
    <x-slot name="title">Editar Aula</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Aula" subtitle="Atualize os dados da aula." />
    @include('professor.aulas.includes._form', ['action' => 'edit', 'classLesson' => $classLesson])
</x-layouts.professor>
