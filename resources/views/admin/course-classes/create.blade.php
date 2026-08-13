<x-layouts.admin>
    <x-slot name="title">Nova Turma</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Turma" subtitle="Defina os dados da turma." />
    @include('admin.course-classes.includes._form', ['action' => 'create', 'courseClass' => null])
</x-layouts.admin>
