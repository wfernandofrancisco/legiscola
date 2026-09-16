@php
    $allowedTabs = ['resumo', 'aulas', 'chamadas', 'matriculas', 'avisos', 'quizzes'];
    $turmaTab = in_array(request()->query('tab'), $allowedTabs, true)
        ? request()->query('tab')
        : 'resumo';

    if ($errors->has('windows')) {
        $turmaTab = 'quizzes';
    } elseif ($errors->hasAny(['body', 'channels', 'subject', 'consent_acknowledged', 'reference_date']) || filled(old('body'))) {
        $turmaTab = 'avisos';
    } elseif (request()->filled('search') || request()->filled('filter_status') || $errors->hasAny(['student_id', 'student_search', 'enrollment_status', 'course_class_id', 'status'])) {
        $turmaTab = 'matriculas';
    } elseif ($errors->hasAny(['lesson_id', 'present_students']) || request()->filled('lesson')) {
        if (! in_array($turmaTab, ['avisos', 'matriculas', 'quizzes'], true)) {
            $turmaTab = 'chamadas';
        }
    }

    $tabUrl = function (string $tab) use ($turma): string {
        $params = ['turma' => $turma, 'tab' => $tab];
        if ($tab === 'chamadas') {
            if (request()->filled('date')) {
                $params['date'] = request('date');
            }
            if (request()->filled('lesson')) {
                $params['lesson'] = request('lesson');
            }
        }
        if ($tab === 'matriculas') {
            if (request()->filled('search')) {
                $params['search'] = request('search');
            }
            if (request()->filled('filter_status')) {
                $params['filter_status'] = request('filter_status');
            }
        }

        return route('admin.turmas.show', $params);
    };

    $hubTabs = [
        'resumo' => 'Resumo',
        'aulas' => 'Aulas',
        'chamadas' => 'Chamadas',
        'matriculas' => 'Matrículas',
        'avisos' => 'Avisos',
        'quizzes' => 'Quizzes',
    ];
@endphp

<x-layouts.admin>
    <x-slot name="title">{{ $turma->name }}</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Gestão da turma</p>
            <h1 class="mt-1 text-pretty text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $turma->name }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Curso: {{ $turma->course?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.turmas.edit', $turma) }}"
               class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 transition-[background-color,transform] duration-150 hover:bg-slate-50 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                Editar dados
            </a>
            <a href="{{ route('admin.aulas.create', ['course_class_id' => $turma->id]) }}"
               class="inline-flex min-h-11 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-[opacity,transform] duration-150 hover:opacity-95 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                Nova aula
            </a>
        </div>
    </div>

    @php
        $licencaRegional = $turma->course?->catalogLicense;
    @endphp

    @if ($licencaRegional)
        <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50/70 p-4 dark:border-indigo-800/60 dark:bg-indigo-950/30 sm:p-5">
            <div class="flex flex-wrap items-center gap-2">
                <x-badge color="violet" text="Conteúdo regional" />
                @if ($licencaRegional->professor_nome)
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Professor: {{ $licencaRegional->professor_nome }}</span>
                @endif
            </div>
            <p class="mt-2 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                O vídeo e o material das aulas ficam no catálogo
                {{ $licencaRegional->director?->name ? 'de '.$licencaRegional->director->name : 'da direção regional' }}
                e o aluno já os vê — mesmo com os campos de vídeo/material em branco aqui.
                A câmara ajusta datas, horários, chamada e matrículas.
            </p>

            <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
                @if ($licencaRegional->exibir_ate)
                    <span>Conteúdo liberado até <strong class="text-slate-800 dark:text-slate-200">{{ $licencaRegional->exibir_ate->format('d/m/Y') }}</strong></span>
                @endif
                <span>
                    Prazo do certificado:
                    <strong class="text-slate-800 dark:text-slate-200">{{ $turma->certificado_disponivel_ate?->format('d/m/Y') ?? 'sem limite' }}</strong>
                </span>
            </div>

            @if (! $turma->certificado_disponivel_ate)
                <a href="{{ route('admin.turmas.edit', $turma) }}"
                   class="mt-3 inline-flex min-h-11 items-center rounded-lg border border-indigo-300 bg-white px-3.5 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-indigo-700 dark:bg-slate-800 dark:text-indigo-200 dark:hover:bg-slate-700">
                    Definir prazo do certificado
                </a>
            @endif
        </div>
    @endif

    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-2 pt-2 backdrop-blur dark:border-slate-700 dark:bg-slate-800/95" role="tablist" aria-label="Seções da turma">
            <div class="flex gap-1 overflow-x-auto pb-px [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($hubTabs as $tabKey => $tabLabel)
                    <a href="{{ $tabUrl($tabKey) }}"
                       role="tab"
                       id="tab-{{ $tabKey }}"
                       aria-selected="{{ $turmaTab === $tabKey ? 'true' : 'false' }}"
                       @class([
                           'shrink-0 rounded-t-lg px-4 py-2.5 text-sm font-semibold transition-[color,background-color,border-color,transform] duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 active:scale-[0.98]',
                           'border-b-2 border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300' => $turmaTab === $tabKey,
                           'border-b-2 border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' => $turmaTab !== $tabKey,
                       ])>
                        {{ $tabLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Aba: Resumo --}}
        @if ($turmaTab === 'resumo')
        <div class="p-4 sm:p-6" role="tabpanel" aria-labelledby="tab-resumo">
            <p class="mb-4 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Tudo da turma em um só lugar. Use as abas para aulas, chamadas, matrículas, avisos e quizzes.
            </p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Matrículas</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $summary['total'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Inscritos</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-blue-600 dark:text-blue-400">{{ $summary['inscrito'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Cursando</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-amber-600 dark:text-amber-400">{{ $summary['cursando'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Concluídos</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $summary['concluido'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Desistiram</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-rose-600 dark:text-rose-400">{{ $summary['desistido'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Aulas</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-slate-600 dark:text-slate-300">{{ ($turmaLessons ?? collect())->count() }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ $tabUrl('aulas') }}" class="rounded-xl border border-slate-200 p-4 transition-[background-color,border-color,transform] duration-150 hover:border-indigo-300 hover:bg-indigo-50/50 active:scale-[0.99] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-600 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/30">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Aulas</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Monte a grade: dia, horário e modalidade.</p>
                </a>
                <a href="{{ $tabUrl('chamadas') }}" class="rounded-xl border border-slate-200 p-4 transition-[background-color,border-color,transform] duration-150 hover:border-indigo-300 hover:bg-indigo-50/50 active:scale-[0.99] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-600 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/30">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Chamadas</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Lance presença por aula.</p>
                </a>
                <a href="{{ $tabUrl('matriculas') }}" class="rounded-xl border border-slate-200 p-4 transition-[background-color,border-color,transform] duration-150 hover:border-indigo-300 hover:bg-indigo-50/50 active:scale-[0.99] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-600 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/30">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Matrículas</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Alunos, status e certificados.</p>
                </a>
                <a href="{{ $tabUrl('avisos') }}" class="rounded-xl border border-slate-200 p-4 transition-[background-color,border-color,transform] duration-150 hover:border-indigo-300 hover:bg-indigo-50/50 active:scale-[0.99] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-600 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/30">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Avisos</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comunique a turma por e-mail ou SMS.</p>
                </a>
            </div>
        </div>
        @endif

        {{-- Aba: Aulas --}}
        @if ($turmaTab === 'aulas')
        <div class="p-4 sm:p-6" role="tabpanel" aria-labelledby="tab-aulas">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Grade desta turma</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Data, horário e modalidade. Presença continua em cada aula desta lista.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.turmas.grade.update', $turma) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @error('grade')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                @forelse ($turmaLessons as $i => $lessonRow)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-600 dark:bg-slate-900/40">
                        <input type="hidden" name="grade[{{ $i }}][class_lesson_id]" value="{{ $lessonRow->id }}">
                        @if ($lessonRow->course_lesson_id)
                            <input type="hidden" name="grade[{{ $i }}][course_lesson_id]" value="{{ $lessonRow->course_lesson_id }}">
                        @endif
                        @if ($lessonRow->catalog_lesson_id)
                            <input type="hidden" name="grade[{{ $i }}][catalog_lesson_id]" value="{{ $lessonRow->catalog_lesson_id }}">
                        @endif
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $lessonRow->title }}</p>
                            <div class="flex flex-wrap gap-2">
                                @if ($lessonRow->isFromCatalog())
                                    <span class="text-xs text-violet-700 dark:text-violet-300">Catálogo regional</span>
                                @elseif ($lessonRow->isFromCourseContent())
                                    <span class="text-xs text-indigo-600 dark:text-indigo-300">Aula do curso</span>
                                @endif
                                <a href="{{ route('admin.turmas.show', ['turma' => $turma, 'tab' => 'chamadas', 'lesson' => $lessonRow->id, 'date' => $lessonRow->date?->format('Y-m-d')]) }}#chamada-aberta"
                                   class="text-xs font-semibold text-slate-600 hover:underline dark:text-slate-300">Abrir chamada</a>
                                <a href="{{ route('admin.aulas.edit', $lessonRow) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Vídeo / material desta turma</a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <x-form.input :name="'grade['.$i.'][date]'" label="Data" type="date" :value="old('grade.'.$i.'.date', $lessonRow->date?->format('Y-m-d'))" />
                            <x-form.input :name="'grade['.$i.'][start_time]'" label="Início" type="time" :value="old('grade.'.$i.'.start_time', $lessonRow->start_time ? substr((string) $lessonRow->start_time, 0, 5) : '')" />
                            <x-form.input :name="'grade['.$i.'][end_time]'" label="Fim" type="time" :value="old('grade.'.$i.'.end_time', $lessonRow->end_time ? substr((string) $lessonRow->end_time, 0, 5) : '')" />
                            <label class="mt-6 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="hidden" name="grade[{{ $i }}][is_online]" value="0">
                                <input type="checkbox" name="grade[{{ $i }}][is_online]" value="1" @checked(old('grade.'.$i.'.is_online', $lessonRow->is_online))>
                                Online
                            </label>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center dark:border-slate-600 dark:bg-slate-900/40">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">Nenhuma aula na grade</p>
                        <p class="mt-1 text-sm text-slate-500">Cadastre as aulas no curso e inclua-as abaixo, ou crie uma aula avulsa.</p>
                    </div>
                @endforelse

                @php $pendingGradeLessons = $pendingGradeLessons ?? []; @endphp
                @foreach ($pendingGradeLessons as $pending)
                    @php $idx = ($turmaLessons->count() ?? 0) + $loop->index; @endphp
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-4 dark:border-amber-800 dark:bg-amber-950/20">
                        @if ($pending['course_lesson_id'])
                            <input type="hidden" name="grade[{{ $idx }}][course_lesson_id]" value="{{ $pending['course_lesson_id'] }}">
                        @endif
                        @if ($pending['catalog_lesson_id'])
                            <input type="hidden" name="grade[{{ $idx }}][catalog_lesson_id]" value="{{ $pending['catalog_lesson_id'] }}">
                        @endif
                        <p class="mb-3 text-sm font-semibold text-amber-900 dark:text-amber-100">Incluir na grade: {{ $pending['title'] }}</p>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <x-form.input :name="'grade['.$idx.'][date]'" label="Data" type="date" :value="old('grade.'.$idx.'.date')" />
                            <x-form.input :name="'grade['.$idx.'][start_time]'" label="Início" type="time" :value="old('grade.'.$idx.'.start_time', '19:00')" />
                            <x-form.input :name="'grade['.$idx.'][end_time]'" label="Fim" type="time" :value="old('grade.'.$idx.'.end_time', '21:00')" />
                            <label class="mt-6 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="hidden" name="grade[{{ $idx }}][is_online]" value="0">
                                <input type="checkbox" name="grade[{{ $idx }}][is_online]" value="1" @checked(old('grade.'.$idx.'.is_online', $turma->tipo_turma === 'online'))>
                                Online
                            </label>
                        </div>
                    </div>
                @endforeach

                @if ($turmaLessons->isNotEmpty() || count($pendingGradeLessons))
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Salvar grade
                        </button>
                        <a href="{{ route('admin.aulas.create', ['course_class_id' => $turma->id]) }}"
                           class="text-sm font-semibold text-slate-600 hover:underline dark:text-slate-300">
                            Aula avulsa (fora do curso)
                        </a>
                    </div>
                @else
                    <a href="{{ route('admin.aulas.create', ['course_class_id' => $turma->id]) }}"
                       class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                        Criar aula avulsa
                    </a>
                @endif
            </form>
        </div>
        @endif

        {{-- Aba: Chamadas --}}
        @if ($turmaTab === 'chamadas')
        <div class="p-4 sm:p-6" role="tabpanel" aria-labelledby="tab-chamadas">
            @if (($lessonSheetLessons ?? collect())->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                    <p class="font-semibold">Cadastre aulas para lançar presença</p>
                    <p class="mt-1 text-xs opacity-90">A chamada fica ligada a cada aula. Vá em <strong>Aulas</strong> e crie a primeira.</p>
                    <a href="{{ $tabUrl('aulas') }}" class="mt-3 inline-flex rounded-lg bg-amber-700 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-800">Ir para aulas</a>
                </div>
            @else
                @include('admin.course-classes.includes._attendance-sheet-chamadas-lesson')
            @endif
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
            <form id="form-matricular-aluno" method="POST" action="{{ route('admin.turmas.matriculas.store', $turma) }}" class="mb-6">
                @csrf
                <x-filter-panel title="Adicionar aluno à turma" subtitle="Digite nome, e-mail ou CPF e clique no aluno na lista antes de matricular.">
                    @if ($errors->hasAny(['student_id', 'course_class_id']))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/50 dark:bg-red-950/30 dark:text-red-300">
                            {{ $errors->first('student_id') ?: $errors->first('course_class_id') }}
                        </div>
                    @endif
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <input type="hidden" id="student_id" name="student_id" value="{{ old('student_id') }}" />
                        <div class="relative md:col-span-2">
                            <x-form.input id="student_search" name="student_search" label="Aluno" :required="true"
                                :value="old('student_search')" autocomplete="off" hint="Digite pelo menos 2 letras e clique no resultado." />
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
                                            <input type="hidden" name="snapshot[professor_nome]"
                                                value="{{ $turma->course?->catalogLicense?->professor_nome
                                                    ?? $turma->teachers?->pluck('full_name')->filter()->implode(', ')
                                                    ?? '' }}">
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

                    var form = document.getElementById('form-matricular-aluno');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            if (!studentId.value) {
                                e.preventDefault();
                                resultsBox.innerHTML =
                                    '<div class="px-3 py-2 text-sm text-red-600">Selecione um aluno na lista antes de matricular.</div>';
                                resultsBox.classList.remove('hidden');
                                studentSearch.focus();
                            }
                        });
                    }
                })();
            </script>
        @endpush
    @endonce
</x-layouts.admin>
