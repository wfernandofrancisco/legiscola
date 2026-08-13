<x-layouts.admin>
    <x-slot name="title">Professores</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Professores" subtitle="Gerencie os professores." :action-href="route('admin.professores.create')" action-text="Novo Professor" />

    <form method="GET" action="{{ route('admin.professores.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa" subtitle="Busque por nome ou especialidade."
            :reset-href="request()->has('search') ? route('admin.professores.index') : null">
            <x-form.input label="Buscar" name="search" value="{{ request('search') }}" placeholder="Nome ou especialidade..." />
        </x-filter-panel>
    </form>

    @include('admin.teachers.includes._table', compact('teachers'))
</x-layouts.admin>
