<x-layouts.aluno title="Inscrições">
    <div class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-indigo-950/40 p-8 shadow-xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400/90">Oferta aberta</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Cursos e eventos disponíveis</h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
            Turmas com inscrições abertas e eventos futuros com inscrição online. Confirme sua vaga aqui mesmo.
        </p>
    </div>

    <div class="space-y-12">
        <section>
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Cursos / turmas</h3>
                <a href="{{ route('portal.cursos.index') }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">Ver no portal</a>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @forelse($courseClasses as $courseClass)
                    @php
                        $jaInscrito = array_key_exists($courseClass->id, $classEnrollments);
                        $livre = $courseClass->max_seats !== null
                            ? max(0, (int) $courseClass->max_seats - (int) $courseClass->matriculas_count)
                            : null;
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-slate-800 bg-slate-900/50 p-5 shadow-lg shadow-black/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-cyan-400/90">{{ $courseClass->course?->name ?? 'Curso' }}</p>
                        <h4 class="mt-2 text-lg font-bold text-white">{{ $courseClass->name }}</h4>
                        <p class="mt-1 text-sm text-slate-400">
                            Inscrições até {{ $courseClass->enrollment_end?->format('d/m/Y H:i') }}
                        </p>
                        @if ($livre !== null)
                            <p class="mt-1 text-xs text-slate-500">{{ $livre }} {{ $livre === 1 ? 'vaga' : 'vagas' }} restante{{ $livre === 1 ? '' : 's' }}</p>
                        @endif
                        @if ($jaInscrito)
                            <span class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-sm font-bold text-slate-500">Já inscrito</span>
                        @else
                            <form method="POST" action="{{ route('app.inscricoes.turmas.store', $courseClass) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:brightness-110">
                                    Inscrever na turma
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-700/80 bg-slate-900/30 p-10 text-center text-sm text-slate-500">
                        Nenhuma turma com inscrições abertas no momento.
                    </div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Eventos</h3>
                <a href="{{ route('portal.eventos.index') }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Ver no portal</a>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @forelse($events as $event)
                    @php $jaInscrito = in_array($event->id, $eventEnrollments, true); @endphp
                    <article class="flex flex-col rounded-2xl border border-slate-800 bg-slate-900/50 p-5 shadow-lg shadow-black/20">
                        @if ($event->city || $event->state)
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-400/90">
                                {{ collect([$event->city, $event->state])->filter()->join(' — ') }}
                            </p>
                        @endif
                        <h4 class="mt-2 text-lg font-bold text-white">{{ $event->title }}</h4>
                        <p class="mt-1 text-sm text-slate-400">{{ $event->date_time?->format('d/m/Y H:i') ?? 'Data a confirmar' }}</p>
                        @if ($jaInscrito)
                            <span class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-sm font-bold text-slate-500">Já inscrito</span>
                        @else
                            <form method="POST" action="{{ route('app.inscricoes.eventos.store', $event) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:brightness-110">
                                    Inscrever no evento
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-700/80 bg-slate-900/30 p-10 text-center text-sm text-slate-500">
                        Nenhum evento com inscrição disponível no momento.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.aluno>
