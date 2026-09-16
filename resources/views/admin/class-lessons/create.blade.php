<x-layouts.admin>
    <x-slot name="title">Nova Aula</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Aula" subtitle="Aula avulsa desta turma. Preferência: cadastre o conteúdo no curso e monte a grade na turma." />
    @include('admin.class-lessons.includes._form', ['action' => 'create', 'classLesson' => null, 'prefillCourseClass' => $prefillCourseClass ?? null])
</x-layouts.admin>
