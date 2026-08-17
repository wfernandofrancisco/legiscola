<x-layouts.aluno :title="$courseClass->name">
    @php
        $dias = ['0' => 'Domingo', '1' => 'Segunda-feira', '2' => 'Terça-feira', '3' => 'Quarta-feira', '4' => 'Quinta-feira', '5' => 'Sexta-feira', '6' => 'Sábado'];
        $course = $courseClass->course;
    @endphp

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('app.turmas.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">← Meus cursos</a>
        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $enrollment->status === 'concluido' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-cyan-500/20 text-cyan-200' }}">{{ $enrollment->status === 'concluido' ? 'Concluído' : 'Cursando' }}</span>
    </div>

    <header class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 p-8">
        <h1 class="text-2xl font-bold text-white sm:text-3xl">{{ $courseClass->name }}</h1>
        <p class="mt-2 text-cyan-300/90">{{ $course?->name }}</p>
        @if ($courseClass->relationLoaded('teachers') && $courseClass->teachers->isNotEmpty())
            <p class="mt-3 text-sm text-slate-400">
                <span class="font-semibold text-slate-300">Docente(s):</span>
                {{ $courseClass->teachers->pluck('full_name')->filter()->implode(', ') }}
            </p>
        @endif
        @if ($course?->description)
            <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-400">{{ $course->description }}</p>
        @endif
        @if ($course?->workload_hours)
            <p class="mt-4 text-sm text-slate-500">Carga horária total: <strong class="text-slate-200">{{ $course->workload_hours }} horas</strong></p>
        @endif

        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-800 bg-slate-950/50 p-4">
                <p class="text-xs font-bold uppercase text-slate-500">Quizzes (aprovação)</p>
                <p class="mt-2 text-3xl font-black text-white">{{ $quizPct !== null ? $quizPct.'%' : '—' }}</p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-indigo-500" style="width: {{ $quizPct ?? 0 }}%"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-950/50 p-4">
                <p class="text-xs font-bold uppercase text-slate-500">Presença (ficha)</p>
                <p class="mt-2 text-3xl font-black text-white">{{ $presencePct !== null ? $presencePct.'%' : '—' }}</p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $presencePct ?? 0 }}%"></div>
                </div>
            </div>
        </div>
    </header>

    @if ($courseClass->satisfaction_survey_id && $courseClass->satisfactionSurvey?->is_active)
        <section class="mb-10">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Pesquisa de satisfação</h2>
            <div class="mt-4 flex flex-col justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-4 sm:flex-row sm:items-center">
                <div class="min-w-0">
                    <p class="font-semibold text-white">{{ $courseClass->satisfactionSurvey->title }}</p>
                    @if ($courseClass->satisfaction_survey_required)
                        <p class="mt-1 text-xs text-amber-300/90">Obrigatória para emissão do certificado</p>
                    @endif
                </div>
                <a href="{{ route('app.pesquisas-satisfacao.show', $courseClass) }}"
                   class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-cyan-500/15 hover:brightness-110">
                    Responder
                </a>
            </div>
        </section>
    @endif

    @if ($courseClass->linkedQuizzes->isNotEmpty())
        <section class="mb-10">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Quizzes desta turma</h2>
            <p class="mt-1 text-xs text-slate-500">Disponibilidade definida pela secretaria em cada turma (datas opcionais).</p>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($courseClass->linkedQuizzes as $qz)
                    @php
                        $p = $qz->pivot;
                        $open = \App\Support\CourseClassQuizAvailability::isOpenNow($p->opens_at, $p->closes_at);
                        $label = \App\Support\CourseClassQuizAvailability::statusLabel($p->opens_at, $p->closes_at);
                    @endphp
                    <li class="flex flex-col justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-4 sm:flex-row sm:items-center">
                        <div class="min-w-0">
                            <p class="font-semibold text-white">{{ $qz->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">Mínimo {{ number_format((float) $qz->min_score_to_pass, 0, ',', '.') }}% para aprovar</p>
                            <span class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $open ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-700/60 text-slate-400' }}">{{ $label }}</span>
                        </div>
                        @if ($open)
                            <a href="{{ route('app.quizzes.show', $qz) }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-cyan-500/15 hover:brightness-110">Responder</a>
                        @else
                            <span class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-xs font-semibold text-slate-500">Fechado</span>
                        @endif
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-slate-600"><a href="{{ route('app.quizzes.index') }}" class="font-semibold text-cyan-400 hover:text-cyan-300">Ver todos os quizzes →</a></p>
        </section>
    @endif

    <div class="grid gap-10 lg:grid-cols-2">
        <section>
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Horário semanal</h2>
            @if ($courseClass->schedules->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Sem grade fixa cadastrada.</p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($courseClass->schedules as $s)
                        <li class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm">
                            <span class="text-slate-200">{{ $dias[(string) $s->weekday] ?? $s->weekday }}</span>
                            <span class="font-mono text-cyan-300/90">{{ \Illuminate\Support\Str::substr($s->start_time, 0, 5) }} — {{ \Illuminate\Support\Str::substr($s->end_time, 0, 5) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Avisos futuros</h2>
            @if ($courseClass->announcements->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Nenhum aviso com data a partir de hoje.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($courseClass->announcements as $a)
                        <li class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                            <p class="text-xs text-slate-500">{{ optional($a->reference_date)->format('d/m/Y') }}</p>
                            <p class="mt-1 font-semibold text-white">{{ $a->subject }}</p>
                            @if ($a->body)
                                <p class="mt-1 text-sm text-slate-400">{{ $a->body }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="mt-12">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Aulas</h2>
        @if ($courseClass->lessons->isEmpty())
            <p class="mt-3 text-sm text-slate-500">Esta turma ainda não possui aulas cadastradas.</p>
        @else
            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($courseClass->lessons as $lesson)
                    <li>
                        <a href="{{ route('app.aulas.show', $lesson) }}" class="block rounded-2xl border border-slate-800 bg-slate-900/60 p-4 transition hover:border-cyan-500/40 hover:bg-slate-900">
                            <p class="font-semibold text-white">{{ $lesson->title }}</p>
                            <p class="mt-1 text-xs text-cyan-300/80">
                                {{ $lesson->date?->format('d/m/Y') }}
                                @if ($lesson->start_time)
                                    · {{ \Illuminate\Support\Str::substr($lesson->start_time, 0, 5) }}
                                @endif
                                @if ($lesson->is_online)
                                    <span class="ml-2 rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-300">Online</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.aluno>
