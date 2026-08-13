<x-layouts.admin>
    <x-slot name="title">Turmas</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Turmas" subtitle="Gerencie as turmas dos cursos." :action-href="route('admin.turmas.create')" action-text="Nova Turma" />

    <form method="GET" action="{{ route('admin.turmas.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Filtre por nome e status."
            :reset-href="request()->hasAny(['search', 'status']) ? route('admin.turmas.index') : null">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input label="Buscar turma" name="search" value="{{ request('search') }}" />
                <x-form.select label="Status" name="status" :options="[
                    'cadastrado' => 'Cadastrado',
                    'inscricao' => 'Inscrição',
                    'em_andamento' => 'Em andamento',
                    'concluido' => 'Concluído',
                    'cancelado' => 'Cancelado',
                ]" :selected="request('status')" />
            </div>
        </x-filter-panel>
    </form>

    @include('admin.course-classes.includes._table', compact('courseClasses'))
</x-layouts.admin>
