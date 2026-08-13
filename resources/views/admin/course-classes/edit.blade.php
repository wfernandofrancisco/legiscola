<x-layouts.admin>
    <x-slot name="title">Editar Turma</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Turma" subtitle="Atualize os dados da turma." />
    @include('admin.course-classes.includes._form', ['action' => 'edit', 'courseClass' => $courseClass])

    <div class="mt-8">
        @include('admin.course-classes.includes._quiz-windows', ['courseClass' => $courseClass])
    </div>
</x-layouts.admin>
