<x-layouts.admin>
    <x-slot name="title">Novo Professor</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Professor" subtitle="Informe os dados do professor." />
    @include('admin.teachers.includes._form', ['action' => 'create', 'teacher' => null])
</x-layouts.admin>
