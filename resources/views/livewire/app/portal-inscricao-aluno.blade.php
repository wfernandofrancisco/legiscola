<x-layouts.aluno title="Inscrições">
    <div class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-indigo-950/40 p-8 shadow-xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400/90">Novas matrículas</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Inscrições abertas</h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
            Inscreva-se em turmas com vagas e período de inscrição ativo, ou em eventos com inscrição online liberada pela escola.
        </p>
    </div>

    <div class="space-y-12">
        <section>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Turmas</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                @forelse($courseClasses as $courseClass)
                    @php $jaInscrito = in_array($courseClass->id, $classEnrollments, true); @endphp
                    <article wire:key="turma-{{ $courseClass->id }}" class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5 shadow-lg shadow-black/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-cyan-400/90">{{ $courseClass->course?->name }}</p>
                        <h4 class="mt-2 text-lg font-bold text-white">{{ $courseClass->name }}</h4>
                        <p class="mt-1 text-sm text-slate-400">
                            {{ $courseClass->enrollment_start?->format('d/m/Y H:i') }} até {{ $courseClass->enrollment_end?->format('d/m/Y H:i') }}
                        </p>
                        <button type="button" wire:click="inscreverEmTurma({{ $courseClass->id }})" @disabled($jaInscrito)
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $jaInscrito ? 'cursor-not-allowed border border-slate-700 bg-slate-800/50 text-slate-500' : 'bg-gradient-to-r from-cyan-500 to-indigo-600 text-white shadow-lg shadow-cyan-500/20 hover:brightness-110' }}">
                            {{ $jaInscrito ? 'Já inscrito' : 'Inscrever na turma' }}
                        </button>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-700/80 bg-slate-900/30 p-10 text-center text-sm text-slate-500">
                        Nenhuma turma com inscrições abertas no momento.
                    </div>
                @endforelse
            </div>
        </section>

        <section>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Eventos</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                @forelse($events as $event)
                    @php $jaInscrito = in_array($event->id, $eventEnrollments, true); @endphp
                    <article wire:key="evento-{{ $event->id }}" class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5 shadow-lg shadow-black/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-400/90">{{ $event->city }} — {{ $event->state }}</p>
                        <h4 class="mt-2 text-lg font-bold text-white">{{ $event->title }}</h4>
                        <p class="mt-1 text-sm text-slate-400">{{ $event->date_time?->format('d/m/Y H:i') }}</p>
                        <button type="button" wire:click="inscreverEmEvento({{ $event->id }})" @disabled($jaInscrito)
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $jaInscrito ? 'cursor-not-allowed border border-slate-700 bg-slate-800/50 text-slate-500' : 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20 hover:brightness-110' }}">
                            {{ $jaInscrito ? 'Já inscrito' : 'Inscrever no evento' }}
                        </button>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-700/80 bg-slate-900/30 p-10 text-center text-sm text-slate-500">
                        Nenhum evento com inscrição online no momento.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.aluno>
