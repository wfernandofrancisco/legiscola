<x-layouts.professor>
    <x-slot name="title">Painel</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            Olá, {{ $teacher?->full_name ?? auth()->user()->name }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Gerencie turmas {{ $teacher ? 'atribuídas a você' : 'do tenant' }}, ficha de presença, aulas, quizzes e avisos. Use o menu à esquerda ou os atalhos abaixo.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <a href="{{ route('professor.turmas.index') }}"
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Turmas</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $turmas->count() }}</p>
            <p class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Ver todas →</p>
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Turmas recentes</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $teacher ? 'Atalhos para as turmas em que você figura como docente.' : 'Turmas recentes deste tenant (gestão).' }}</p>
        </div>
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($turmas as $t)
                <li class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $t->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t->course?->name ?? '—' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('professor.turmas.show', $t) }}"
                            class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">Abrir</a>
                        <a href="{{ route('professor.turmas.ficha-presenca', ['turma' => $t, 'date' => now()->toDateString(), 'tab' => 'chamadas']) }}"
                            class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Presença</a>
                        <a href="{{ route('professor.aulas.index', ['course_class_id' => $t->id]) }}"
                            class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Aulas</a>
                    </div>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Nenhuma turma disponível aqui {{ $teacher ? '— solicite o vínculo à coordenação.' : '.' }}
                </li>
            @endforelse
        </ul>
        @if ($turmas->isNotEmpty())
            <div class="border-t border-gray-200 px-6 py-3 dark:border-gray-700">
                <a href="{{ route('professor.turmas.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Listagem completa de turmas →</a>
            </div>
        @endif
    </div>
</x-layouts.professor>
