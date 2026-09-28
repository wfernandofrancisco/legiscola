<x-layouts.admin>
    <x-slot name="title">Aulas</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Aulas da turma" subtitle="Grade (data e horário) de cada turma. O conteúdo fica no curso ou no catálogo." :action-href="route('admin.aulas.create')" action-text="Aula avulsa" />

    @if (($turmasComAulasPendentes ?? collect())->isNotEmpty())
        <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30" role="status">
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Há aulas do curso que ainda não estão na grade de nenhuma data</p>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">
                As aulas cadastradas no curso só aparecem aqui depois de incluídas na grade da turma, com data e horário.
            </p>
            <ul class="mt-3 space-y-2">
                @foreach ($turmasComAulasPendentes as $turma)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/70 px-3 py-2 text-sm dark:bg-slate-900/40">
                        <span class="text-slate-800 dark:text-slate-100">
                            <strong>{{ $turma->name }}</strong>
                            @if ($turma->course)
                                <span class="text-slate-500 dark:text-slate-400">· {{ $turma->course->name }}</span>
                            @endif
                            <span class="text-amber-700 dark:text-amber-300">· {{ $turma->aulas_pendentes }} {{ $turma->aulas_pendentes === 1 ? 'aula pendente' : 'aulas pendentes' }}</span>
                        </span>
                        <a href="{{ route('admin.turmas.show', ['turma' => $turma, 'tab' => 'aulas']) }}"
                           class="inline-flex min-h-9 items-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                            Montar a grade
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.aulas.index') }}" class="mb-6">
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

    @include('admin.class-lessons.includes._table', compact('classLessons'))
</x-layouts.admin>
