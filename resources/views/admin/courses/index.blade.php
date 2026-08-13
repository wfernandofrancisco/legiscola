<x-layouts.admin>
    <x-slot name="title">Cursos</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search'), request('status')])->filter(fn($v) => filled($v))->count();
        $headerItems = [
            ['title' => 'cursos', 'value' => $courses->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];
    @endphp

    <x-page-header title="Cursos" subtitle="Gerencie os cursos da Escola Legislativa." :items="$headerItems"
        :action-href="route('admin.cursos.create')" action-text="Novo Curso" />

    <form method="GET" action="{{ route('admin.cursos.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Busque por nome ou descrição."
            :reset-href="request()->hasAny(['search', 'status']) ? route('admin.cursos.index') : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="lg:col-span-4">
                    <x-form.input label="Buscar curso" name="search" value="{{ request('search') }}" autocomplete="off"
                        placeholder="Nome ou descrição..." />
                </div>
                <div class="lg:col-span-1">
                    <x-form.select label="Status" name="status" placeholder="Todos" :options="[
                        'rascunho' => 'Rascunho',
                        'ativo' => 'Ativo',
                        'inativo' => 'Inativo',
                        'arquivado' => 'Arquivado',
                    ]" :selected="request('status')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    @include('admin.courses.includes._table', compact('courses'))
</x-layouts.admin>
