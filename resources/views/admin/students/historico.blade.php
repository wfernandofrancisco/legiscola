@php
    $statusUi = [
        'inscrito' => ['label' => 'Inscrito', 'color' => 'blue'],
        'cursando' => ['label' => 'Cursando', 'color' => 'yellow'],
        'concluido' => ['label' => 'Concluído', 'color' => 'green'],
        'desistido' => ['label' => 'Desistiu', 'color' => 'red'],
        'baixa_presenca' => ['label' => 'Baixa presença', 'color' => 'gray'],
    ];
@endphp

<x-layouts.admin>
    <x-slot name="title">Histórico do aluno</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-page-header
        :title="$student->user?->name ?? 'Aluno'"
        subtitle="Toque em um curso para ver as aulas e a presença. Eventos ficam na lista abaixo."
        :action-href="route('admin.alunos.edit', $student)"
        action-text="Editar cadastro"
    />

    <dl class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">E-mail</dt>
            <dd class="mt-1 truncate text-sm font-medium text-gray-900 dark:text-white">{{ $student->email }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">CPF</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $student->cpf ?: '—' }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Matrícula</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $student->enrollment_number ?: '—' }}</dd>
        </div>
    </dl>

    <section id="cursos" class="mb-12" x-data="{ open: null }">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Cursos e turmas</h2>
                <p class="mt-0.5 text-sm text-gray-500">{{ $enrollments->total() }} {{ $enrollments->total() === 1 ? 'vínculo' : 'vínculos' }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @forelse ($turmaRows as $row)
                @php $ui = $statusUi[$row['status']] ?? ['label' => ucfirst((string) $row['status']), 'color' => 'gray']; @endphp
                <div class="border-b border-gray-100 last:border-b-0 dark:border-gray-700">
                    <button type="button"
                        class="flex w-full items-center gap-4 px-4 py-4 text-left transition hover:bg-gray-50 sm:px-5 dark:hover:bg-gray-900/40"
                        @click="open = open === {{ $row['id'] }} ? null : {{ $row['id'] }}"
                        :aria-expanded="open === {{ $row['id'] }}">
                        <span class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-sm font-black text-indigo-600 sm:flex dark:bg-indigo-950/50 dark:text-indigo-300"
                              x-text="open === {{ $row['id'] }} ? '−' : '+'"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">{{ $row['course'] }}</span>
                            <span class="mt-0.5 block truncate text-base font-bold text-gray-900 dark:text-white">{{ $row['turma'] }}</span>
                            <span class="mt-1 block text-xs text-gray-500 sm:hidden">
                                Presença {{ $row['presencePct'] === null ? '—' : $row['presencePct'].'%' }}
                            </span>
                        </span>
                        <span class="hidden w-28 shrink-0 sm:block">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Presença</span>
                            <span class="mt-1 flex items-center gap-2">
                                <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <span class="block h-full rounded-full bg-emerald-500" style="width: {{ $row['presencePct'] ?? 0 }}%"></span>
                                </span>
                                <span class="w-9 text-right text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['presencePct'] === null ? '—' : $row['presencePct'].'%' }}</span>
                            </span>
                        </span>
                        <span class="shrink-0">
                            <x-badge :color="$ui['color']" :text="$ui['label']" />
                        </span>
                        <svg class="h-5 w-5 shrink-0 text-gray-400 transition" :class="open === {{ $row['id'] }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open === {{ $row['id'] }}" x-cloak x-transition.opacity>
                        <div class="border-t border-gray-100 bg-gray-50/80 px-4 py-4 sm:px-5 dark:border-gray-700 dark:bg-gray-900/30">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-gray-500">{{ $row['lessonCount'] }} {{ $row['lessonCount'] === 1 ? 'aula' : 'aulas' }}</p>
                                <a href="{{ route('admin.turmas.show', $row['turmaId']) }}?tab=matriculas"
                                   class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Abrir turma</a>
                            </div>
                            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900/60 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2">Aula</th>
                                            <th class="px-4 py-2">Data</th>
                                            <th class="px-4 py-2">Horário</th>
                                            <th class="px-4 py-2">Presença</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @forelse ($row['lessons'] as $lesson)
                                            <tr>
                                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $lesson['title'] }}</td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300">{{ $lesson['date'] ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300">
                                                    {{ $lesson['start'] ?? '—' }}
                                                    @if ($lesson['end'])
                                                        – {{ $lesson['end'] }}
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    @if (! $lesson['hasRecord'])
                                                        <span class="text-xs text-gray-400">Sem registro</span>
                                                    @elseif ($lesson['present'])
                                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Presente</span>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">Ausente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Nenhuma aula cadastrada nesta turma.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-gray-500">Nenhuma turma vinculada a este aluno.</p>
            @endforelse
        </div>

        @if ($enrollments->hasPages())
            <div class="mt-4">{{ $enrollments->fragment('cursos')->links() }}</div>
        @endif
    </section>

    <section id="eventos">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Eventos</h2>
            <p class="mt-0.5 text-sm text-gray-500">{{ $eventEnrollments->total() }} {{ $eventEnrollments->total() === 1 ? 'inscrição' : 'inscrições' }}</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Evento</th>
                        <th class="px-5 py-3">Data</th>
                        <th class="hidden px-5 py-3 sm:table-cell">Cidade</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($eventEnrollments as $eventEnrollment)
                        @php $event = $eventEnrollment->event; @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/30">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $event->title }}</td>
                            <td class="px-5 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $event->date_time?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="hidden px-5 py-3 text-gray-600 sm:table-cell dark:text-gray-300">{{ $event->city ?: '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($eventEnrollment->presente)
                                    <x-badge color="green" text="Presente" />
                                @else
                                    <x-badge color="blue" text="Inscrito" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Nenhum evento inscrito.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($eventEnrollments->hasPages())
            <div class="mt-4">{{ $eventEnrollments->fragment('eventos')->links() }}</div>
        @endif
    </section>
</x-layouts.admin>
