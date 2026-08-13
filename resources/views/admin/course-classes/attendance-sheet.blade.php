@php
    $fichaTab = 'chamadas';
    if ($errors->hasAny(['body', 'channels', 'subject', 'consent_acknowledged', 'reference_date']) || filled(old('body'))) {
        $fichaTab = 'avisos';
    } else {
        $rawTab = (string) request()->query('tab', '');
        if ($rawTab === '') {
            $rawTab = (string) request()->query('amp;tab', 'chamadas');
        }
        $rawTab = strtolower(trim($rawTab));
        if (str_starts_with($rawTab, 'avisos')) {
            $fichaTab = 'avisos';
        }
    }

    $refDate = $lessonActiveLesson?->date?->format('Y-m-d') ?? $date;
    $fichaChamadasParams = ['turma' => $turma, 'date' => $refDate, 'tab' => 'chamadas'];
    if ($lessonActiveLesson ?? null) {
        $fichaChamadasParams['lesson'] = $lessonActiveLesson->id;
    }
    $fichaChamadasUrl = route('admin.turmas.ficha-presenca', $fichaChamadasParams);

    $fichaAvisosParams = ['turma' => $turma, 'date' => $refDate, 'tab' => 'avisos'];
    if ($lessonActiveLesson ?? null) {
        $fichaAvisosParams['lesson'] = $lessonActiveLesson->id;
    }
    $fichaAvisosUrl = route('admin.turmas.ficha-presenca', $fichaAvisosParams);
@endphp

<x-layouts.admin>
    <x-slot name="title">Chamadas por aula</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-page-header :title="'Chamadas — ' . $turma->name" :subtitle="'Curso: ' . ($turma->course?->name ?? '—') . ' · Uma chamada por aula cadastrada.'" />

    @if ($lessonSheetLessons->isEmpty())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
            <p class="font-semibold">Cadastre aulas para esta turma</p>
            <p class="mt-1 text-xs opacity-90">Em <strong>Escola → Aulas</strong>, vincule aulas a esta turma. A presença (admin ou confirmação do aluno em aulas online) fica ligada a cada aula.</p>
        </div>
    @else
        <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
            <p class="font-semibold">Como funciona</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-xs leading-relaxed opacity-95">
                <li><strong>Presencial:</strong> secretaria lança a chamada aqui e pode imprimir a ficha em PDF.</li>
                <li><strong>Online</strong> (turma online ou aula marcada como online): o aluno confirma presença na página da aula; você pode ajustar a lista aqui se precisar.</li>
            </ul>
        </div>
    @endif

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-wrap gap-1 border-b border-gray-200 px-2 pt-2 dark:border-gray-700" role="tablist">
            <a href="{{ $fichaChamadasUrl }}" role="tab" aria-selected="{{ $fichaTab === 'chamadas' ? 'true' : 'false' }}"
                class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                @class([
                    'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' => $fichaTab === 'chamadas',
                    'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' => $fichaTab !== 'chamadas',
                ])>
                Chamadas (presença)
            </a>
            <a href="{{ $fichaAvisosUrl }}" role="tab" aria-selected="{{ $fichaTab === 'avisos' ? 'true' : 'false' }}"
                class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                @class([
                    'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' => $fichaTab === 'avisos',
                    'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' => $fichaTab !== 'avisos',
                ])>
                Envio de aviso
            </a>
        </div>

        @if ($fichaTab === 'chamadas')
        <div class="p-4 sm:p-6">
            @if ($lessonSheetLessons->isEmpty())
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                    <p class="font-semibold">Nenhuma aula cadastrada para esta turma</p>
                    <p class="mt-1 text-xs opacity-90">
                        Cadastre aulas em <strong>Escola → Aulas</strong> (vincule à turma) ou rode o seeder
                        <code class="rounded bg-amber-100/80 px-1 py-0.5 text-[11px] dark:bg-amber-900/50">ClassLessonSeeder</code>.
                        A aba <strong>Chamadas</strong> só exibe o formulário quando existir pelo menos uma aula com data.
                    </p>
                    <p class="mt-3">
                        <a href="{{ route('admin.aulas.create') }}?course_class_id={{ $turma->id }}"
                            class="inline-flex rounded-lg bg-amber-700 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-800">
                            Nova aula para esta turma
                        </a>
                    </p>
                </div>
            @else
                @include('admin.course-classes.includes._attendance-sheet-chamadas-lesson')
            @endif

            <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                <a href="{{ route('admin.turmas.show', $turma) }}"
                    class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Voltar à triagem
                </a>
            </div>
        </div>
        @endif

        @if ($fichaTab === 'avisos')
        <div class="p-4 sm:p-6">
            @include('admin.course-classes.includes._announcement-form', ['turma' => $turma, 'defaultReferenceDate' => $refDate])

            @if (isset($recentAnnouncements) && $recentAnnouncements->isNotEmpty())
                <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-900/40">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Avisos recentes desta turma</h3>
                    <div class="mt-3 overflow-x-auto text-xs">
                        <table class="w-full min-w-[32rem] text-left">
                            <thead class="border-b border-gray-200 text-gray-500 dark:border-gray-600 dark:text-gray-400">
                                <tr>
                                    <th class="py-2 pr-3">Quando</th>
                                    <th class="py-2 pr-3">Por</th>
                                    <th class="py-2 pr-3">Canais</th>
                                    <th class="py-2 pr-3">Ref.</th>
                                    <th class="py-2 pr-3">Entregas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($recentAnnouncements as $ann)
                                    <tr>
                                        <td class="py-2 pr-3 text-gray-800 dark:text-gray-200">{{ $ann->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="py-2 pr-3">{{ $ann->createdBy?->name ?? '—' }}</td>
                                        <td class="py-2 pr-3">{{ implode(', ', $ann->channels ?? []) }}</td>
                                        <td class="py-2 pr-3">{{ $ann->reference_date?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="py-2 pr-3">{{ $ann->deliveries_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Ainda não há avisos registrados para esta turma.</p>
            @endif

            <p class="mt-6 text-center">
                <a href="{{ $fichaChamadasUrl }}"
                    class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                    ← Voltar para Chamadas
                </a>
            </p>
        </div>
        @endif
    </div>
</x-layouts.admin>
