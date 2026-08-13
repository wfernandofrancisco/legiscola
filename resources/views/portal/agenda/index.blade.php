@extends('layouts.portal')

@section('title', 'Agenda')

@section('content')
    @php
        $wdShort = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
        $mesAtual = $monthStart->format('Y-m');
        $urlMes = fn (string $param) => route('portal.agenda.index', ['mes' => $param]);
    @endphp

    <x-portal.page-hero
        title="Agenda"
        subtitle="Eventos institucionais e encontros recorrentes das turmas (com base nos horários cadastrados e no período letivo ou de inscrições)."
    />

    <section class="no-portal-animate border-b border-slate-200/80 bg-gradient-to-b from-slate-50 to-white py-10 dark:border-slate-800 dark:from-slate-950 dark:to-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em]" style="color:var(--portal-primary)">Calendário mensal</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $monthLabel }}</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="get" action="{{ route('portal.agenda.index') }}" class="flex items-center gap-2">
                    <label for="agenda-mes" class="sr-only">Mês</label>
                    <input id="agenda-mes" type="month" name="mes" value="{{ $mesAtual }}"
                           class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300/50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                    <button type="submit"
                            class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-bold text-white shadow-md transition hover:opacity-95"
                            style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                        Filtrar
                    </button>
                </form>
                <div class="flex gap-2">
                    <a href="{{ $urlMes($prevParam) }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-500">
                        ← Mês anterior
                    </a>
                    <a href="{{ $urlMes($nextParam) }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-500">
                        Próximo mês →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="no-portal-animate py-12 sm:py-16" data-animate="fadeInUp">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xl shadow-slate-900/5 ring-1 ring-slate-100 dark:border-slate-800 dark:bg-slate-950/50 dark:ring-white/5">
                <div class="grid grid-cols-7 border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900">
                    @foreach($wdShort as $h)
                        <div class="px-1 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 sm:px-2 sm:text-xs dark:text-slate-400">
                            {{ $h }}
                        </div>
                    @endforeach
                </div>
                @foreach($weeks as $week)
                    <div class="grid grid-cols-7 divide-x divide-slate-100 border-b border-slate-100 last:border-b-0 dark:divide-slate-800 dark:border-slate-800">
                        @foreach($week as $cell)
                            @php
                                /** @var \Carbon\CarbonImmutable $d */
                                $d = $cell['date'];
                                $isToday = $d->isToday();
                            @endphp
                            <div @class([
                                'min-h-[5.5rem] p-1.5 sm:min-h-[7.5rem] sm:p-2',
                                'bg-slate-50/80 dark:bg-slate-900/40' => ! $cell['inMonth'],
                                'bg-white dark:bg-slate-950/30' => $cell['inMonth'],
                            ])>
                                <div class="flex items-start justify-between gap-1">
                                    <span @class([
                                        'inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-lg text-xs font-black sm:h-8 sm:min-w-[2rem] sm:text-sm',
                                        'text-slate-400 dark:text-slate-600' => ! $cell['inMonth'],
                                        'text-slate-800 dark:text-slate-100' => $cell['inMonth'] && ! $isToday,
                                        'text-white shadow-md ring-2 ring-white/30' => $isToday,
                                    ]) @if($isToday) style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))" @endif>
                                        {{ $d->day }}
                                    </span>
                                </div>
                                <ul class="mt-1.5 space-y-1">
                                    @foreach(array_slice($cell['items'], 0, 4) as $item)
                                        <li>
                                            <a href="{{ $item['href'] }}"
                                               class="block truncate rounded-md px-1 py-0.5 text-[10px] font-semibold leading-tight shadow-sm ring-1 transition hover:opacity-95 sm:text-[11px]"
                                               @if(($item['kind'] ?? '') === 'event')
                                                   style="background:color-mix(in srgb,var(--portal-tertiary,#34d399) 18%,white);color:rgb(15 23 42);ring-color:color-mix(in srgb,var(--portal-tertiary) 40%,transparent)"
                                               @else
                                                   style="background:color-mix(in srgb,var(--portal-primary,#3b82f6) 14%,white);color:rgb(15 23 42);ring-color:color-mix(in srgb,var(--portal-primary) 38%,transparent)"
                                               @endif
                                               title="{{ $item['title'] }} — {{ $item['timeLabel'] }}">
                                                <span class="font-mono text-[9px] opacity-80 sm:text-[10px]">{{ $item['timeLabel'] }}</span>
                                                <span class="block truncate">{{ $item['title'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                    @if(count($cell['items']) > 4)
                                        <li class="px-1 text-[9px] font-bold text-slate-500 dark:text-slate-400">+{{ count($cell['items']) - 4 }} itens</li>
                                    @endif
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex flex-wrap gap-6 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-5 py-4 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full shadow-sm ring-2 ring-white dark:ring-slate-900" style="background:var(--portal-primary)"></span>
                    <span class="font-medium text-slate-700 dark:text-slate-200">Turma (horário recorrente)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full shadow-sm ring-2 ring-white dark:ring-slate-900" style="background:var(--portal-tertiary)"></span>
                    <span class="font-medium text-slate-700 dark:text-slate-200">Evento</span>
                </div>
            </div>

            <p class="mt-6 text-center text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                Turmas: o período usa as datas das aulas cadastradas quando existirem; caso contrário, o intervalo de inscrições.
                Horários repetem-se por dia da semana dentro desse período.
                <a href="{{ route('portal.eventos.index') }}" class="font-semibold underline decoration-slate-300 underline-offset-2" style="color:var(--portal-primary)">Lista de eventos</a>
                ·
                <a href="{{ route('portal.cursos.index') }}" class="font-semibold underline decoration-slate-300 underline-offset-2" style="color:var(--portal-primary)">Turmas e cursos</a>
            </p>
        </div>
    </section>
@endsection
