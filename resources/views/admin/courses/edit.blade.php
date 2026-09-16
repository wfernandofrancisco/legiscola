<x-layouts.admin>
    <x-slot name="title">Editar Curso</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Curso" subtitle="Dados do curso e aulas de conteúdo. Data e horário entram na turma." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.courses.includes._form', ['action' => 'edit', 'course' => $course])
        @if ($course->isFromCatalog())
            <div class="mt-8 rounded-lg border border-violet-200 bg-violet-50/80 p-5 text-sm text-violet-900 dark:border-violet-800 dark:bg-violet-950/30 dark:text-violet-200">
                Este curso veio do catálogo regional. As aulas de conteúdo ficam com o diretor;
                nesta câmara você só monta a <strong>grade da turma</strong> (dias e horários).
            </div>
        @else
            @include('admin.courses.includes._lessons', ['course' => $course])
        @endif
    </div>
</x-layouts.admin>
