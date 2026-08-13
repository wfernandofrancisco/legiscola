<x-layouts.professor>
    <x-slot name="title">Minhas turmas</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Minhas turmas" subtitle="Turmas em que você está cadastrado como docente." />

    @include('professor.turmas.includes._table', ['turmas' => $turmas])
</x-layouts.professor>
