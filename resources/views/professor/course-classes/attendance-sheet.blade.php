@php
    $fichaTab = 'chamadas';
    if ($errors->hasAny(['body', 'channels', 'subject', 'consent_acknowledged', 'reference_date']) || filled(old('body'))) {
        $fichaTab = 'avisos';
    } else {
        $rawTab = (string) request()->query('tab', 'chamadas');
        $rawTab = str_replace('&amp;', '&', $rawTab);
        if (($amp = strpos($rawTab, '&')) !== false) {
            $rawTab = substr($rawTab, 0, $amp);
        }
        $rawTab = strtolower(trim($rawTab));
        if ($rawTab === 'avisos') {
            $fichaTab = 'avisos';
        }
    }

    $refDate = $lessonActiveLesson?->date?->format('Y-m-d') ?? $date;
    $fichaChamadasParams = ['turma' => $turma, 'date' => $refDate, 'tab' => 'chamadas'];
    if ($lessonActiveLesson ?? null) {
        $fichaChamadasParams['lesson'] = $lessonActiveLesson->id;
    }
    $fichaChamadasUrl = route('professor.turmas.ficha-presenca', $fichaChamadasParams);

    $fichaAvisosParams = ['turma' => $turma, 'date' => $refDate, 'tab' => 'avisos'];
    if ($lessonActiveLesson ?? null) {
        $fichaAvisosParams['lesson'] = $lessonActiveLesson->id;
    }
    $fichaAvisosUrl = route('professor.turmas.ficha-presenca', $fichaAvisosParams);
@endphp

<x-layouts.professor>
    <x-slot name="title">Chamadas por aula</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-page-header :title="'Chamadas — ' . $turma->name" :subtitle="'Curso: ' . ($turma->course?->name ?? '—') . ' · Uma chamada por aula cadastrada.'" />

    @if ($lessonSheetLessons->isEmpty())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
            <p class="font-semibold">Nenhuma aula com data para esta turma</p>
            <p class="mt-1 text-xs opacity-90">
                Cronograma semanal (admin) não gera lista aqui automaticamente — use <strong>Cadastrar aula rápido</strong> na aba Chamadas ou o menu <strong>Aulas</strong>.
            </p>
        </div>
    @else
        <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
            <p class="font-semibold">Como funciona</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-xs leading-relaxed opacity-95">
                <li><strong>Presencial:</strong> lance a chamada aqui e imprima a ficha em PDF.</li>
                <li><strong>Online</strong> (turma online ou aula marcada como online): o aluno pode confirmar presença no portal; você pode ajustar aqui.</li>
            </ul>
        </div>
    @endif

    {{--
        Abas por link (reload): evita Alpine @click quebrando o atributo HTML quando @js(URL) inclui aspas duplas
        dentro de @click="... replaceState(..., '', \"...\")" — os botões deixavam de responder ao clique.
    --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-wrap gap-1 border-b border-gray-200 px-2 pt-2 dark:border-gray-700" role="tablist">
            <a href="{{ $fichaChamadasUrl }}" role="tab" aria-selected="{{ $fichaTab === 'chamadas' ? 'true' : 'false' }}"
                @class([
                    'inline-flex rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
                    'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' => $fichaTab === 'chamadas',
                    'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:border-transparent dark:text-gray-400 dark:hover:text-gray-200' => $fichaTab !== 'chamadas',
                ])>
                Chamadas (presença)
            </a>
            <a href="{{ $fichaAvisosUrl }}" role="tab" aria-selected="{{ $fichaTab === 'avisos' ? 'true' : 'false' }}"
                @class([
                    'inline-flex rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
                    'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' => $fichaTab === 'avisos',
                    'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:border-transparent dark:text-gray-400 dark:hover:text-gray-200' => $fichaTab !== 'avisos',
                ])>
                Envio de aviso
            </a>
        </div>

        @if ($fichaTab === 'chamadas')
        <div class="p-4 sm:p-6">
            @include('professor.course-classes.includes._ficha-presenca-nova-aula', [
                'turma' => $turma,
                'date' => $date,
                'defaultScheduleStart' => $defaultScheduleStart ?? '19:00',
                'defaultScheduleEnd' => $defaultScheduleEnd ?? '22:00',
            ])

            @if ($lessonSheetLessons->isEmpty() && isset($weeklyScheduleSlots) && $weeklyScheduleSlots->isNotEmpty())
                <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50/80 p-4 text-sm text-sky-950 dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-100">
                    <p class="font-semibold">Esta turma tem horário semanal no cadastro, mas ainda não há aulas com data.</p>
                    <p class="mt-1 text-xs opacity-95">
                        Use o bloco <strong>Cadastrar aula rápido</strong> acima (ou o admin em <strong>Aulas</strong>). Só então a chamada por aula aparece nesta aba.
                    </p>
                </div>
            @endif

            @if ($lessonSheetLessons->isNotEmpty())
                @include('professor.course-classes.includes._attendance-sheet-chamadas-lesson')
            @endif

            <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                <a href="{{ route('professor.turmas.show', $turma) }}"
                    class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Voltar à turma
                </a>
            </div>
        </div>
        @else
        <div class="p-4 sm:p-6">
            @include('professor.course-classes.includes._announcement-form', ['turma' => $turma, 'defaultReferenceDate' => $refDate])

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
</x-layouts.professor>
