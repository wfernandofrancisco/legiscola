<x-layouts.aluno title="Meus eventos">
    <div class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-violet-950/40 p-8 shadow-xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-wider text-violet-300/90">Eventos</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Meus eventos</h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
            Acompanhe as inscrições e, quando o evento tiver chamada por georreferência, registre sua presença no local e no horário liberados.
        </p>
    </div>

    @if ($enrollments->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-700/80 bg-slate-900/30 p-12 text-center">
            <p class="text-base font-semibold text-slate-200">Nenhuma inscrição em evento</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                Quando você se inscrever em um evento pelo portal ou pela área de inscrições, ele aparecerá aqui.
            </p>
            <a href="{{ route('app.inscricoes.index') }}"
               class="mt-6 inline-flex rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-500">
                Ver inscrições disponíveis
            </a>
        </div>
    @else
        <ul class="grid gap-5 sm:grid-cols-2">
            @foreach ($enrollments as $enrollment)
                @php
                    $event = $enrollment->event;
                    $geo = $event->isGeofenceCheckInEnabled();
                    $windowOpen = $geo && $event->isPresenceWindowOpen();
                @endphp
                <li class="flex flex-col overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/50 shadow-lg shadow-black/20">
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($enrollment->presente)
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300 ring-1 ring-emerald-400/25">Presente</span>
                            @elseif ($windowOpen)
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-300 ring-1 ring-amber-400/25">Chamada aberta</span>
                            @elseif ($geo)
                                <span class="rounded-full bg-slate-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 ring-1 ring-slate-500/25">Georreferência</span>
                            @else
                                <span class="rounded-full bg-violet-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-300 ring-1 ring-violet-400/25">Inscrito</span>
                            @endif
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-white">{{ $event->title }}</h3>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ $event->date_time?->format('d/m/Y H:i') ?? '—' }}
                            @if ($event->city)
                                · {{ $event->city }}@if($event->state)/{{ $event->state }}@endif
                            @endif
                        </p>
                        @if ($geo && $event->presenca_inicio_em && $event->presenca_fim_em)
                            <p class="mt-2 text-[11px] text-slate-500">
                                Chamada: {{ $event->presenca_inicio_em->format('d/m/Y H:i') }}
                                até {{ $event->presenca_fim_em->format('d/m/Y H:i') }}
                            </p>
                        @endif
                        <div class="mt-6">
                            <a href="{{ route('app.eventos.show', $event) }}"
                               class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-violet-500 to-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-violet-500/20 transition hover:brightness-110">
                                {{ $enrollment->presente ? 'Ver detalhes' : ($windowOpen ? 'Registrar presença' : 'Abrir evento') }}
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.aluno>
