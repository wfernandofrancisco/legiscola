@props(['s', 'theme' => 'sky'])
@php
    $rootClass = 'overflow-hidden rounded-3xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900';
@endphp

@php
    $v = $s->status->value;
    $cancel = $v === 'cancelado';
    $final = $v === 'finalizado';
    $acordoFeito = in_array($v, ['fechado', 'agendado', 'finalizado', 'divergente'], true)
        || ($cancel && $s->fechado_em !== null);
    $agendamentoOk = $s->agendamento_aprovado_morador_em !== null
        || in_array($v, ['agendado', 'finalizado', 'divergente'], true);
    $concluidoFunil = $final || $cancel || $v === 'divergente';
    $steps = [
        ['label' => 'Pedido', 'done' => true],
        ['label' => 'Acordo', 'done' => $acordoFeito || $cancel],
        ['label' => 'Agendado', 'done' => $agendamentoOk || ($cancel && $s->agendamento_data_hora)],
        ['label' => 'Encerrado', 'done' => $concluidoFunil],
    ];
    $bar = $theme === 'blue' ? 'from-blue-600 to-cyan-500' : 'from-sky-500 to-indigo-500';
@endphp

<div {{ $attributes->merge(['class' => $rootClass]) }}>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Andamento</h2>
    </div>
    <ol class="flex flex-wrap gap-2 sm:gap-3">
        @foreach ($steps as $i => $st)
            <li class="flex min-w-0 flex-1 basis-[45%] items-center gap-2 rounded-2xl border px-3 py-2 sm:basis-0 sm:shrink sm:flex-1
                {{ $st['done'] ? 'border-emerald-200 bg-emerald-50/90 dark:border-emerald-900/50 dark:bg-emerald-950/30' : 'border-slate-100 bg-slate-50 dark:border-gray-700 dark:bg-gray-800/60' }}">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-black
                    {{ $st['done'] ? 'bg-gradient-to-br '.$bar.' text-white shadow' : 'bg-white text-slate-400 ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-gray-600' }}">
                    {{ $i + 1 }}
                </span>
                <span class="truncate text-[11px] font-bold leading-tight text-slate-800 dark:text-slate-100">{{ $st['label'] }}</span>
            </li>
        @endforeach
    </ol>
</div>
