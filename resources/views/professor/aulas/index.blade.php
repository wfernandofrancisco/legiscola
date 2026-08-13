<x-layouts.professor>
    <x-slot name="title">Aulas</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Aulas da Turma" subtitle="Gerencie as aulas presenciais e EAD." :action-href="route('professor.aulas.create')" action-text="Nova Aula" />

    <form method="GET" action="{{ route('professor.aulas.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Filtre por título ou turma.">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="search" label="Buscar aula" value="{{ request('search') }}" />
                <x-form.select name="course_class_id" label="Turma" :selected="request('course_class_id')">
                    <option value="">Todas</option>
                    @foreach($courseClasses as $courseClass)
                        <option value="{{ $courseClass->id }}" @selected(request('course_class_id') == $courseClass->id)>
                            {{ $courseClass->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
        </x-filter-panel>
    </form>

    @include('professor.aulas.includes._table', compact('classLessons'))
</x-layouts.professor>
