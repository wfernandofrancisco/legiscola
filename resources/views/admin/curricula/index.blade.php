<x-layouts.admin>
    <x-slot name="title">Grade Curricular</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search'), request('course_id')])->filter(fn($v) => filled($v))->count();
        $headerItems = [
            ['title' => 'disciplinas', 'value' => $curricula->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];
    @endphp

    <x-page-header title="Grade Curricular" subtitle="Gerencie disciplinas e módulos dos cursos." :items="$headerItems"
        :action-href="route('admin.grades-curriculares.create')" action-text="Nova Disciplina" />

    <form method="GET" action="{{ route('admin.grades-curriculares.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Busque por disciplina ou curso."
            :reset-href="request()->hasAny(['search', 'course_id']) ? route('admin.grades-curriculares.index') : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <x-form.input label="Buscar disciplina" name="search" value="{{ request('search') }}" autocomplete="off"
                        placeholder="Nome da disciplina..." />
                </div>
                <div class="lg:col-span-2">
                    <x-form.select label="Curso" name="course_id" placeholder="Todos os cursos" :options="$courses->pluck('name', 'id')->toArray()"
                        :selected="request('course_id')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    @include('admin.curricula.includes._table', compact('curricula'))
</x-layouts.admin>
