<x-layouts.admin>
    <x-slot name="title">Editar Professor</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Professor" subtitle="Atualize os dados do professor." />
    @include('admin.teachers.includes._form', ['action' => 'edit', 'teacher' => $teacher])
</x-layouts.admin>
