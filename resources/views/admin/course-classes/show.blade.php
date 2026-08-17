@php
    $turmaTab = in_array(request()->query('tab'), ['resumo', 'avisos', 'matriculas', 'quizzes'], true)
        ? request()->query('tab')
        : 'resumo';

    if ($errors->has('windows')) {
        $turmaTab = 'quizzes';
    } elseif ($errors->hasAny(['body', 'channels', 'subject', 'consent_acknowledged', 'reference_date']) || filled(old('body'))) {
        $turmaTab = 'avisos';
    } elseif (request()->filled('search') || request()->filled('filter_status') || $errors->has('student_id') || $errors->has('enrollment_status')) {
        $turmaTab = 'matriculas';
    }

    $tabUrl = fn (string $tab) => route('admin.turmas.show', array_merge(['turma' => $turma], request()->except('page'), ['tab' => $tab]));
@endphp

<x-layouts.admin>
    <x-slot name="title">Triagem da Turma</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-page-header :title="'Triagem - ' . $turma->name" :subtitle="'Curso: ' . ($turma->course?->name ?? '—')" />

    <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-wrap gap-1 border-b border-gray-200 px-2 pt-2 dark:border-gray-700" role="tablist">
            <a href="{{ $tabUrl('resumo') }}" role="tab" aria-selected="{{ $turmaTab === 'resumo' ? 'true' : 'false' }}"
                class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $turmaTab === 'resumo' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Resumo
            </a>
            <a href="{{ $tabUrl('avisos') }}" role="tab" aria-selected="{{ $turmaTab === 'avisos' ? 'true' : 'false' }}"
                class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $turmaTab === 'avisos' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Avisos
            </a>
            <a href="{{ $tabUrl('matriculas') }}" role="tab" aria-selected="{{ $turmaTab === 'matriculas' ? 'true' : 'false' }}"
                class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $turmaTab === 'matriculas' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Matrículas
            </a>
            <a href="{{ $tabUrl('quizzes') }}" role="tab" aria-selected="{{ $turmaTab === 'quizzes' ? 'true' : 'false' }}"
                class="rounded-t-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $turmaTab === 'quizzes' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Quizzes
            </a>
        </div>

        {{-- Aba: Resumo --}}
        @if ($turmaTab === 'resumo')
        <div class="p-4 sm:p-6">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Indicadores da turma; use as outras abas para avisos e gestão de alunos.</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Matrículas</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['total'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Inscritos</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['inscrito'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cursando</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $summary['cursando'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Concluídos</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $summary['concluido'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Desistiram</p>
                    <p class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $summary['desistido'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Baixa presença</p>
                    <p class="mt-1 text-2xl font-bold text-slate-600 dark:text-slate-300">{{ $summary['baixa_presenca'] }}</p>
                </div>
            </div>
            <p class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('admin.turmas.show', $turma) }}?tab=quizzes"
                    class="font-semibold text-indigo-600 underline decoration-indigo-400/60 underline-offset-2 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Abrir aba Quizzes
                </a>
                — definir quando os alunos podem responder (vínculos feitos na tela de cada quiz).
            </p>
        </div>
        @endif

        {{-- Aba: Avisos --}}
        @if ($turmaTab === 'avisos')
        <div class="p-4 sm:p-6">
            @include('admin.course-classes.includes._announcement-form', ['turma' => $turma, 'defaultReferenceDate' => null])

            @if (isset($recentAnnouncements) && $recentAnnouncements->isNotEmpty())
                <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-900/40">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Avisos recentes</h3>
                    <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Histórico por turma; status <em>queued</em> = e-mail aceito na fila de envio.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[32rem] text-left text-xs">
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
        </div>
        @endif

        {{-- Aba: Quizzes (janelas por turma) --}}
        @if ($turmaTab === 'quizzes')
        <div class="p-4 sm:p-6">
            @include('admin.course-classes.includes._quiz-windows', ['courseClass' => $turma])
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                Para vincular esta turma a um quiz, use
                <a href="{{ route('admin.quizzes.index') }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Quizzes</a>
                (edição do quiz → turmas). Editar dados da turma:
                <a href="{{ route('admin.turmas.edit', $turma) }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Editar turma</a>.
            </p>
        </div>
        @endif

        {{-- Aba: Matrículas --}}
        @if ($turmaTab === 'matriculas')
        <div class="p-4 sm:p-6">
            <form method="POST" action="{{ route('admin.turmas.matriculas.store', $turma) }}" class="mb-6">
                @csrf
                <x-filter-panel title="Adicionar aluno à turma" subtitle="Matricule manualmente com triagem inicial.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <input type="hidden" id="student_id" name="student_id" value="{{ old('student_id') }}" />
                        <div class="relative md:col-span-2">
                            <x-form.input id="student_search" name="student_search" label="Aluno" :required="true"
                                :value="old('student_search')" autocomplete="off" hint="Digite para buscar alunos ativos." />
                            <div id="student-search-results"
                                class="absolute z-30 mt-1 hidden max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                            </div>
                        </div>
                        <x-form.select name="status" label="Status inicial" :options="[
                            'inscrito' => 'Inscrito',
                            'cursando' => 'Cursando',
                        ]" :selected="old('status', 'inscrito')" />
                        <x-form.input name="observations" label="Observação" :value="old('observations')" />
                    </div>
                    <div class="mt-4">
                        <button type="submit"
                            class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                            Matricular aluno
                        </button>
                    </div>
                </x-filter-panel>
            </form>

            <form method="GET" action="{{ route('admin.turmas.show', $turma) }}" class="mb-6">
                <input type="hidden" name="tab" value="matriculas">
                <x-filter-panel title="Pesquisa e filtros" subtitle="Encontre matrículas por aluno e status." :reset-href="request()->hasAny(['search', 'filter_status']) ? route('admin.turmas.show', $turma) : null">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-form.input label="Buscar aluno" name="search" value="{{ request('search') }}" />
                        <x-form.select label="Status" name="filter_status" :options="[
                            'inscrito' => 'Inscrito',
                            'cursando' => 'Cursando',
                            'desistido' => 'Desistido',
                            'concluido' => 'Concluído',
                            'baixa_presenca' => 'Baixa Presença',
                        ]" :selected="request('filter_status')" />
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="submit"
                            class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                            Filtrar
                        </button>
                        <a href="{{ $tabUrl('resumo') }}"
                            class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Ver resumo
                        </a>
                    </div>
                </x-filter-panel>
            </form>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                @if ($errors->has('enrollment_status'))
                    <div class="mx-4 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
                        {{ $errors->first('enrollment_status') }}
                    </div>
                @endif
                <div class="m-4 flex flex-wrap items-center justify-end gap-3">
                    <form method="POST" action="{{ route('admin.turmas.matriculas.concluir-inscritos', $turma) }}"
                        onsubmit="return confirm('Deseja marcar todos os alunos inscritos como concluídos?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            @disabled($turma->status !== 'concluido')
                            class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                            Concluir todos os inscritos
                        </button>
                    </form>
                </div>

                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Aluno</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Presença até hoje</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Observações</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($enrollments as $enrollment)
                            @php
                                $statusMap = [
                                    'inscrito' => ['label' => 'Inscrito', 'color' => 'blue'],
                                    'cursando' => ['label' => 'Cursando', 'color' => 'yellow'],
                                    'desistido' => ['label' => 'Desistido', 'color' => 'red'],
                                    'concluido' => ['label' => 'Concluído', 'color' => 'green'],
                                    'baixa_presenca' => ['label' => 'Baixa Presença', 'color' => 'gray'],
                                ][$enrollment->status] ?? ['label' => ucfirst($enrollment->status), 'color' => 'gray'];

                                $attendancePercent = $attendancePercentageByStudent[$enrollment->student_id] ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $enrollment->student?->user?->name ?? $enrollment->student?->email ?? '—' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $enrollment->student?->email ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4"><x-badge :color="$statusMap['color']" :text="$statusMap['label']" /></td>
                                <td class="px-6 py-4">
                                    @if ($totalAttendanceDates > 0)
                                        @php
                                            $presenceColor = ($attendancePercent ?? 0) >= 75 ? 'green' : (($attendancePercent ?? 0) >= 50 ? 'yellow' : 'red');
                                        @endphp
                                        <x-badge :color="$presenceColor" :text="($attendancePercent ?? 0) . '%'" />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Base: {{ $totalAttendanceDates }} aula(s) cadastrada(s) na turma
                                        </p>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Sem aulas cadastradas na turma</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $enrollment->observations ?: '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST"
                                        action="{{ route('admin.turmas.matriculas.status', [$turma, $enrollment]) }}"
                                        class="flex flex-wrap items-center justify-end gap-2" id="enrollment-status-{{ $enrollment->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="enrollment_status"
                                            class="rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                            <option value="inscrito" @selected($enrollment->status === 'inscrito')>Inscrito</option>
                                            <option value="cursando" @selected($enrollment->status === 'cursando')>Cursando</option>
                                            <option value="desistido" @selected($enrollment->status === 'desistido')>Desistido</option>
                                            <option value="concluido" @selected($enrollment->status === 'concluido')
                                                @disabled($turma->status !== 'concluido' || (($attendancePercent ?? 0) < 75 && $enrollment->status !== 'concluido'))>Concluído</option>
                                            <option value="baixa_presenca" @selected($enrollment->status === 'baixa_presenca')>Baixa Presença</option>
                                        </select>
                                        @if (($attendancePercent ?? 0) < 75)
                                            <span class="text-[11px] text-amber-600 dark:text-amber-300">
                                                Mínimo para concluir: 75%
                                            </span>
                                        @endif
                                        <input type="text" name="observations" value="{{ $enrollment->observations }}"
                                            placeholder="Observação"
                                            class="rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
                                        <button type="submit"
                                            class="inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">Salvar</button>
                                        @php
                                            $latestCertificateHash = $latestCertificateHashByStudent[$enrollment->student_id] ?? null;
                                            $surveyOk = ! $turma->requiresSatisfactionSurvey()
                                                || ! empty(($surveyCompletedByStudent ?? [])[(int) $enrollment->student_id]);
                                        @endphp
                                        @if ($enrollment->status === 'concluido')
                                            @if ($latestCertificateHash)
                                                <a href="{{ route('certificados.download', $latestCertificateHash) }}" target="_blank"
                                                    class="inline-flex rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    Baixar certificado
                                                </a>
                                            @elseif (! $surveyOk)
                                                <span
                                                    class="inline-flex cursor-not-allowed rounded-lg bg-amber-100 px-3 py-2 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                                                    title="O aluno ainda não respondeu a pesquisa de satisfação obrigatória.">
                                                    Pesquisa pendente
                                                </span>
                                            @elseif ($activeCertificateTemplate)
                                                <button type="submit" form="issue-certificate-{{ $enrollment->id }}"
                                                    formtarget="_blank"
                                                    class="inline-flex rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                                                    Emitir certificado
                                                </button>
                                            @else
                                                <span
                                                    class="inline-flex cursor-not-allowed rounded-lg bg-slate-300 px-3 py-2 text-xs font-semibold text-slate-700"
                                                    title="Aluno concluído, mas não há template de certificado ativo para emissão.">
                                                    Sem template ativo
                                                </span>
                                            @endif
                                        @endif
                                    </form>
                                    @if ($enrollment->status === 'concluido' && ! $latestCertificateHash && $activeCertificateTemplate && $surveyOk)
                                        <form method="POST" action="{{ route('admin.escola.certificados.issue') }}"
                                            id="issue-certificate-{{ $enrollment->id }}" class="hidden">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}">
                                            <input type="hidden" name="course_id" value="{{ $turma->course_id }}">
                                            <input type="hidden" name="certificate_template_id"
                                                value="{{ $activeCertificateTemplate->id }}">
                                            <input type="hidden" name="snapshot[student_name]"
                                                value="{{ $enrollment->student?->user?->name ?? 'Aluno' }}">
                                            <input type="hidden" name="snapshot[course_name]"
                                                value="{{ $turma->course?->name ?? 'Curso' }}">
                                            <input type="hidden" name="snapshot[workload_hours]"
                                                value="{{ (int) ($turma->course?->workload_hours ?? 0) }}">
                                            <input type="hidden" name="redirect_to_download" value="1">
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">Nenhuma matrícula encontrada
                                    para esta turma.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($enrollments->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        {{ $enrollments->links() }}
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    @once
        @push('scripts')
            <script>
                (function() {
                    var studentSearch = document.getElementById('student_search');
                    var studentId = document.getElementById('student_id');
                    var resultsBox = document.getElementById('student-search-results');
                    if (!studentSearch || !studentId || !resultsBox) return;

                    var timer;
                    var searchUrl = '{{ route('admin.turmas.alunos.search', $turma) }}';

                    function hideResults() {
                        resultsBox.classList.add('hidden');
                        resultsBox.innerHTML = '';
                    }

                    function renderResults(items) {
                        if (!Array.isArray(items) || !items.length) {
                            resultsBox.innerHTML =
                                '<div class="px-3 py-2 text-sm text-gray-500">Nenhum aluno encontrado.</div>';
                        } else {
                            resultsBox.innerHTML = items.map(function(student) {
                                return '<button type="button" data-id="' + student.id + '" data-name="' + student.name
                                    .replace(/"/g, '&quot;') +
                                    '" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200"><div class="font-medium">' +
                                    student.name + '</div><div class="text-xs text-gray-500">' + (student.email || '') +
                                    '</div></button>';
                            }).join('');
                        }
                        resultsBox.classList.remove('hidden');
                    }

                    function fetchStudents(q) {
                        resultsBox.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Buscando...</div>';
                        resultsBox.classList.remove('hidden');

                        var url = searchUrl + '?q=' + encodeURIComponent(q);
                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                renderResults(data);
                            })
                            .catch(function() {
                                resultsBox.innerHTML =
                                    '<div class="px-3 py-2 text-sm text-red-500">Erro ao buscar alunos.</div>';
                            });
                    }

                    studentSearch.addEventListener('input', function() {
                        studentId.value = '';
                        clearTimeout(timer);
                        var q = studentSearch.value.trim();
                        if (q.length < 2) {
                            hideResults();
                            return;
                        }
                        timer = setTimeout(function() {
                            fetchStudents(q);
                        }, 300);
                    });

                    studentSearch.addEventListener('focus', function() {
                        var q = studentSearch.value.trim();
                        if (q.length >= 2) {
                            clearTimeout(timer);
                            timer = setTimeout(function() {
                                fetchStudents(q);
                            }, 100);
                        }
                    });

                    resultsBox.addEventListener('click', function(e) {
                        var btn = e.target.closest('button[data-id]');
                        if (!btn) return;
                        studentId.value = btn.getAttribute('data-id');
                        studentSearch.value = btn.getAttribute('data-name');
                        hideResults();
                    });

                    document.addEventListener('click', function(e) {
                        if (!resultsBox.contains(e.target) && e.target !== studentSearch) {
                            hideResults();
                        }
                    });
                })();
            </script>
        @endpush
    @endonce
</x-layouts.admin>
