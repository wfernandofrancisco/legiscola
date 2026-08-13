<x-layouts.admin>
    <x-slot name="title">Alunos</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search')])->filter(fn($v) => filled($v))->count();
        $headerItems = [
            ['title' => 'alunos', 'value' => $students->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];
    @endphp

    <x-page-header title="Alunos" subtitle="Gerencie os estudantes vinculados aos usuários do tenant." :items="$headerItems"
        :action-href="route('admin.alunos.create')" action-text="Novo Aluno" />

    <form method="GET" action="{{ route('admin.alunos.index') }}" class="mb-6" x-data="{}">
        <x-filter-panel title="Pesquisa" subtitle="Busque por nome, email ou matrícula — atualiza ao digitar."
            :reset-href="request()->filled('search') ? route('admin.alunos.index') : null">
            <x-form.input label="Buscar aluno" name="search" value="{{ request('search') }}" autocomplete="off"
                placeholder="Nome, email ou matrícula..."
                x-on:input.debounce.350ms="$el.form.requestSubmit()" />
        </x-filter-panel>
    </form>

    @include('admin.students.includes._table', compact('students'))
</x-layouts.admin>
