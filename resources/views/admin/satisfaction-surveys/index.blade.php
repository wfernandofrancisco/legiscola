<x-layouts.admin>
    <x-slot name="title">Pesquisas de satisfação</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search'), request('status')])->filter(fn ($value) => filled($value))->count();
        $headerItems = [
            ['title' => 'pesquisas', 'value' => $surveys->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];
    @endphp

    <x-page-header title="Pesquisas de satisfação" subtitle="Crie pesquisas e vincule nas turmas para avaliar a experiência dos alunos."
        :items="$headerItems" :action-href="route('admin.pesquisas-satisfacao.create')" action-text="Nova pesquisa" />

    <form method="GET" action="{{ route('admin.pesquisas-satisfacao.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Encontre pesquisas por título."
            :reset-href="request()->hasAny(['search', 'status']) ? route('admin.pesquisas-satisfacao.index') : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="lg:col-span-4">
                    <x-form.input label="Buscar" name="search" value="{{ request('search') }}" autocomplete="off"
                        placeholder="Título da pesquisa..." />
                </div>
                <div class="lg:col-span-1">
                    <x-form.select label="Status" name="status" placeholder="Todos"
                        :options="['1' => 'Ativa', '0' => 'Inativa']" :selected="request('status')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    @include('admin.satisfaction-surveys.includes._table', compact('surveys'))
</x-layouts.admin>
