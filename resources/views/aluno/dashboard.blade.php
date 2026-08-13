<x-layouts.aluno title="Início">
    @if (! $student)
        <div class="rounded-3xl border border-amber-500/30 bg-amber-500/10 p-8 text-center">
            <p class="text-lg font-semibold text-white">Olá, {{ $user->name }}</p>
            <p class="mt-2 text-sm text-amber-100/80">Não encontramos seu cadastro de aluno vinculado a esta conta. Fale com a secretaria da escola.</p>
        </div>
    @else
        <div class="mb-8 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-900/40 p-8 shadow-xl shadow-black/30">
            <p class="text-sm font-medium text-cyan-300/90">Bem-vindo de volta</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $user->name }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-400">Acompanhe avisos da turma, próximas aulas, desempenho nos quizzes e presença — tudo em um só lugar.</p>
        </div>

        @if ($enrollmentSnapshots->isNotEmpty())
            <div class="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($enrollmentSnapshots as $snap)
                    @php
                        $cc = $snap['courseClass'];
                        $quiz = $snap['quizPct'];
                        $pres = $snap['presencePct'];
                    @endphp
                    <a href="{{ route('app.turmas.show', $cc) }}" class="group rounded-2xl border border-slate-800 bg-slate-900/60 p-5 transition hover:border-cyan-500/40 hover:bg-slate-900">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Turma</p>
                        <p class="mt-1 font-bold text-white group-hover:text-cyan-200">{{ $cc->name }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $cc->course?->name }}</p>
                        <div class="mt-4 space-y-3">
                            <div>
                                <div class="flex justify-between text-[11px] font-semibold text-slate-400">
                                    <span>Quizzes (aprovação)</span>
                                    <span>{{ $quiz !== null ? $quiz.'%' : '—' }}</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-indigo-500 transition-all" style="width: {{ $quiz ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[11px] font-semibold text-slate-400">
                                    <span>Presença (ficha)</span>
                                    <span>{{ $pres !== null ? $pres.'%' : '—' }}</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all" style="width: {{ $pres ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-2">
            <section>
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Avisos (próximas 2 semanas)</h3>
                </div>
                @if ($announcements->isEmpty())
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 text-sm text-slate-500">Nenhum aviso com data de referência neste período.</div>
                @else
                    <ul class="space-y-3">
                        @foreach ($announcements as $a)
                            <li class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span class="rounded-full bg-cyan-500/15 px-2 py-0.5 font-semibold text-cyan-300">{{ $a->courseClass?->name }}</span>
                                    <span>{{ optional($a->reference_date)->format('d/m/Y') }}</span>
                                </div>
                                <p class="mt-2 font-semibold text-white">{{ $a->subject }}</p>
                                @if ($a->body)
                                    <p class="mt-1 text-sm text-slate-400 line-clamp-4">{{ $a->body }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section>
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Próximas aulas</h3>
                    <a href="{{ route('app.turmas.index') }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">Ver cursos</a>
                </div>
                @if ($upcomingLessons->isEmpty())
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 text-sm text-slate-500">Sem aulas agendadas a partir de hoje ou a turma ainda não cadastrou o cronograma de aulas.</div>
                @else
                    <ul class="space-y-3">
                        @foreach ($upcomingLessons as $lesson)
                            <li>
                                <a href="{{ route('app.aulas.show', $lesson) }}" class="flex items-start justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-4 transition hover:border-cyan-500/35 hover:bg-slate-900">
                                    <div>
                                        <p class="font-semibold text-white">{{ $lesson->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $lesson->courseClass?->name }}</p>
                                        <p class="mt-2 text-xs font-medium text-cyan-300/90">
                                            {{ $lesson->date?->format('d/m/Y') }}
                                            @if ($lesson->start_time)
                                                · {{ \Illuminate\Support\Str::substr($lesson->start_time, 0, 5) }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-lg bg-white/5 px-2 py-1 text-[10px] font-bold uppercase text-slate-400">Abrir</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    @endif
</x-layouts.aluno>
