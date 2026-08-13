<x-layouts.aluno title="Meus cursos">
    @php
        $dias = ['0' => 'Domingo', '1' => 'Segunda-feira', '2' => 'Terça-feira', '3' => 'Quarta-feira', '4' => 'Quinta-feira', '5' => 'Sexta-feira', '6' => 'Sábado'];
        $availableCourseClasses = $availableCourseClasses ?? collect();
        $studentEnrollmentStatuses = $studentEnrollmentStatuses ?? [];
    @endphp

    <p class="mb-6 text-sm text-slate-400">Acompanhe suas turmas e encontre novos cursos com inscrições abertas.</p>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-semibold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm font-semibold text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <section class="mb-12">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-cyan-400/90">Novas inscrições</p>
                <h2 class="mt-1 text-xl font-bold text-white">Cursos disponíveis</h2>
            </div>
            <a href="{{ route('app.inscricoes.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">Ver central de inscrições →</a>
        </div>

        @if ($availableCourseClasses->isEmpty())
            <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/30 p-8 text-center text-sm text-slate-500">
                Nenhuma turma com inscrições abertas no momento.
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($availableCourseClasses as $availableClass)
                    @php
                        $course = $availableClass->course;
                        $matriculas = (int) ($availableClass->matriculas_count ?? 0);
                        $vagas = $availableClass->max_seats !== null ? max(0, (int) $availableClass->max_seats - $matriculas) : null;
                        $enrollmentStatus = $studentEnrollmentStatuses[$availableClass->id] ?? null;
                    @endphp
                    <article class="rounded-3xl border border-slate-800 bg-slate-900/50 p-5 shadow-lg shadow-black/20">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-400/90">{{ $course?->name }}</p>
                                <h3 class="mt-2 text-lg font-bold text-white">{{ $availableClass->name }}</h3>
                                <p class="mt-2 text-sm text-slate-400">
                                    Inscrições até {{ $availableClass->enrollment_end?->format('d/m/Y H:i') }}
                                </p>
                                @if ($vagas !== null)
                                    <span class="mt-3 inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-slate-200">{{ $vagas }} vaga{{ $vagas === 1 ? '' : 's' }}</span>
                                @else
                                    <span class="mt-3 inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-slate-200">Vagas ilimitadas</span>
                                @endif
                            </div>

                            @if ($enrollmentStatus)
                                <span class="inline-flex rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm font-bold text-emerald-200">
                                    Já inscrito
                                </span>
                            @elseif ($vagas !== 0)
                                <form method="POST" action="{{ route('app.turmas.inscrever', $availableClass) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-cyan-500/15 transition hover:brightness-110">
                                        Inscrever-me
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex rounded-xl border border-slate-700 px-4 py-2 text-sm font-bold text-slate-500">Sem vagas</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section>
        <div class="mb-4">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Acompanhamento</p>
            <h2 class="mt-1 text-xl font-bold text-white">Minhas turmas</h2>
        </div>

    @if ($rows->isEmpty())
        <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-10 text-center text-slate-500">Você ainda não está matriculado em nenhuma turma.</div>
    @else
        <div class="space-y-6">
            @foreach ($rows as $row)
                @php
                    $e = $row['enrollment'];
                    $cc = $row['courseClass'];
                    $course = $cc->course;
                    $quiz = $row['quizPct'];
                    $pres = $row['presencePct'];
                @endphp
                <article class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/50 shadow-xl shadow-black/20">
                    <div class="border-b border-slate-800/80 bg-gradient-to-r from-slate-900 to-slate-900/40 px-6 py-5 sm:flex sm:items-start sm:justify-between sm:gap-4">
                        <div>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $e->status === 'concluido' ? 'bg-emerald-500/20 text-emerald-300' : ($e->status === 'inscrito' ? 'bg-amber-500/20 text-amber-200' : 'bg-cyan-500/20 text-cyan-200') }}">
                                {{ ['inscrito' => 'Inscrito', 'cursando' => 'Cursando', 'concluido' => 'Concluído'][$e->status] ?? ucfirst($e->status) }}
                            </span>
                            <h2 class="mt-2 text-xl font-bold text-white">{{ $cc->name }}</h2>
                            <p class="mt-1 text-sm text-cyan-300/80">{{ $course?->name }}</p>
                            @if ($course?->workload_hours)
                                <p class="mt-2 text-xs text-slate-500">Carga horária: <span class="font-semibold text-slate-300">{{ $course->workload_hours }}h</span></p>
                            @endif
                        </div>
                        <a href="{{ route('app.turmas.show', $cc) }}" class="mt-4 inline-flex shrink-0 items-center justify-center rounded-xl bg-white/10 px-4 py-2 text-sm font-bold text-white ring-1 ring-white/10 transition hover:bg-cyan-500 hover:text-slate-950 hover:ring-0 sm:mt-0">
                            Abrir turma
                        </a>
                    </div>
                    <div class="grid gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Horário fixo (semana)</h3>
                            @if ($cc->schedules->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">Nenhum horário cadastrado.</p>
                            @else
                                <ul class="mt-3 space-y-2 text-sm text-slate-300">
                                    @foreach ($cc->schedules as $s)
                                        <li class="flex justify-between gap-2 rounded-xl bg-slate-950/50 px-3 py-2">
                                            <span>{{ $dias[(string) $s->weekday] ?? 'Dia '.$s->weekday }}</span>
                                            <span class="font-mono text-xs text-cyan-200/90">{{ \Illuminate\Support\Str::substr($s->start_time, 0, 5) }} — {{ \Illuminate\Support\Str::substr($s->end_time, 0, 5) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-400">
                                    <span>Progresso nos quizzes</span>
                                    <span>{{ $quiz !== null ? $quiz.'%' : 'Sem quizzes' }}</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-indigo-500" style="width: {{ $quiz ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-400">
                                    <span>Presença (ficha)</span>
                                    <span>{{ $pres !== null ? $pres.'%' : 'Sem registros' }}</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $pres ?? 0 }}%"></div>
                                </div>
                            </div>
                            @if ($cc->lessons->isNotEmpty())
                                <p class="text-xs text-slate-500">{{ $cc->lessons->count() }} aula(s) cadastrada(s) nesta turma.</p>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
    </section>
</x-layouts.aluno>
