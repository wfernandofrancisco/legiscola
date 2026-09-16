<x-layouts.admin>
    <x-slot name="title">Eventos</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Eventos" subtitle="Gerencie eventos e palestras. Palestra liberada pela direção aparece aqui para agendar." :action-href="route('admin.eventos.create')" action-text="Novo Evento" />

    @php
        $palestrasLiberadas = ($licencasPendentes ?? collect())->filter(fn ($l) => $l->catalogItem?->isPalestra());
    @endphp

    @if ($palestrasLiberadas->isNotEmpty())
        <section class="mb-6 rounded-2xl border border-cyan-200 bg-cyan-50/80 p-5 dark:border-cyan-800/50 dark:bg-cyan-950/30 sm:p-6"
            aria-label="Palestras liberadas pela direção regional">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <x-badge color="cyan" text="Direção regional" />
                    <h2 class="mt-2 text-base font-semibold text-slate-900 dark:text-white">
                        {{ $palestrasLiberadas->count() }}
                        {{ $palestrasLiberadas->count() === 1 ? 'palestra liberada' : 'palestras liberadas' }} para agendar
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        A direção já liberou o conteúdo. Defina data, local e inscrições para ela aparecer na agenda pública.
                    </p>
                </div>
            </div>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($palestrasLiberadas as $licenca)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-cyan-200 bg-white px-4 py-3 dark:border-cyan-800 dark:bg-slate-900">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $licenca->catalogItem?->titulo }}</p>
                            @if ($licenca->exibir_ate)
                                <p class="text-xs text-slate-500">Disponível até {{ $licenca->exibir_ate->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        <a href="{{ route('admin.catalogo-regional.show', $licenca) }}"
                            class="inline-flex min-h-10 shrink-0 items-center rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-500">
                            Agendar palestra
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <form method="GET" action="{{ route('admin.eventos.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa" subtitle="Busque por título."
            :reset-href="request()->has('search') ? route('admin.eventos.index') : null">
            <x-form.input label="Buscar evento" name="search" value="{{ request('search') }}" />
        </x-filter-panel>
    </form>

    @include('admin.events.includes._table', compact('events'))
</x-layouts.admin>
