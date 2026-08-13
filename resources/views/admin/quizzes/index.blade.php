<x-layouts.admin>
    <x-slot name="title">Quizzes</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search'), request('status')])->filter(fn($value) => filled($value))->count();
        $headerItems = [
            ['title' => 'quizzes', 'value' => $quizzes->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];
    @endphp

    <x-page-header title="Quizzes" subtitle="Gerencie quizzes e vincule as turmas." :items="$headerItems"
        :action-href="route('admin.quizzes.create')" action-text="Novo Quiz" />

    <form method="GET" action="{{ route('admin.quizzes.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Encontre quizzes por titulo."
            :reset-href="request()->hasAny(['search', 'status']) ? route('admin.quizzes.index') : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="lg:col-span-4">
                    <x-form.input label="Buscar quiz" name="search" value="{{ request('search') }}" autocomplete="off"
                        placeholder="Titulo do quiz..." />
                </div>
                <div class="lg:col-span-1">
                    <x-form.select label="Status" name="status" placeholder="Todos"
                        :options="['1' => 'Ativo', '0' => 'Inativo']" :selected="request('status')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    @include('admin.quizzes.includes._table', compact('quizzes'))
</x-layouts.admin>
