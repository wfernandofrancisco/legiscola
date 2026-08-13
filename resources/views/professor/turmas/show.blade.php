<x-layouts.professor>
    <x-slot name="title">{{ $turma->name }}</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header :title="$turma->name" :subtitle="'Curso: ' . ($turma->course?->name ?? '—')" />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Docentes da turma</h2>
                @if ($turma->teachers->isEmpty())
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Nenhum docente vinculado além do cadastro padrão.</p>
                @else
                    <ul class="mt-3 space-y-2 text-sm text-gray-800 dark:text-gray-200">
                        @foreach ($turma->teachers as $doc)
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                {{ $doc->full_name }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Próximas aulas (cadastro)</h2>
                @if ($turma->lessons->isEmpty())
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Nenhuma aula cadastrada ainda.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($turma->lessons->take(8) as $lesson)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $lesson->title }}</span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $lesson->date?->format('d/m/Y') }}
                                    @if ($lesson->start_time)
                                        · {{ \Illuminate\Support\Str::substr($lesson->start_time, 0, 5) }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        <a href="{{ route('professor.aulas.index', ['course_class_id' => $turma->id]) }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Ver e editar todas as aulas →</a>
                    </p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('professor.turmas.ficha-presenca', ['turma' => $turma, 'date' => now()->toDateString(), 'tab' => 'chamadas']) }}"
                class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-600">
                <span class="text-2xl">📋</span>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Ficha de presença</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Lançar chamada e imprimir</p>
                </div>
            </a>
            <a href="{{ route('professor.aulas.index', ['course_class_id' => $turma->id]) }}"
                class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-600">
                <span class="text-2xl">📅</span>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Aulas</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Lançar encontros desta turma</p>
                </div>
            </a>
            <a href="{{ route('professor.quizzes.index') }}"
                class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-600">
                <span class="text-2xl">📝</span>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Quizzes</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Montar e vincular às turmas</p>
                </div>
            </a>
            <a href="{{ route('professor.turmas.ficha-presenca', ['turma' => $turma, 'date' => now()->toDateString(), 'tab' => 'avisos']) }}"
                class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-600">
                <span class="text-2xl">📣</span>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Avisos</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Demanda de aviso na ficha (aba Avisos)</p>
                </div>
            </a>
        </div>
    </div>
</x-layouts.professor>
