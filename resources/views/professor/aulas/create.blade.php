<x-layouts.professor>
    <x-slot name="title">Nova Aula</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Aula" subtitle="Defina aula, data e modalidade." />
    @include('professor.aulas.includes._form', [
        'action' => 'create',
        'classLesson' => null,
        'prefillCourseClassId' => $prefillCourseClassId ?? null,
        'prefillCourseClassName' => $prefillCourseClassName ?? '',
    ])
</x-layouts.professor>
