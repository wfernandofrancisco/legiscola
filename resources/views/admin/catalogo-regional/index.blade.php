<x-layouts.admin>
    <x-slot name="title">Conteúdo regional</x-slot>

    @php
        $disponiveis = $licencas->filter(fn($l) => $l->isUsable());
    @endphp

    <x-page-header title="Conteúdo regional"
        subtitle="Cursos e palestras liberados pela direção regional para a sua câmara."
        :items="[
            ['title' => 'liberados', 'value' => $licencas->count(), 'color' => 'sky'],
            ['title' => 'disponíveis agora', 'value' => $disponiveis->count(), 'color' => 'emerald'],
        ]" />

    @if ($licencas->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhum conteúdo liberado até agora.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Quando a direção regional liberar um curso ou palestra para a sua câmara, ele aparece aqui.
            </p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($licencas as $licenca)
                @php
                    $item = $licenca->catalogItem;
                    $ehPalestra = $item->tipo === \App\Enums\CatalogItemTipo::Palestra;
                    $edicoes = $ehPalestra
                        ? $licenca->events->count()
                        : ($licenca->course?->courseClasses->count() ?? 0);
                    $restantes = $ehPalestra ? $licenca->eventosRestantes() : $licenca->turmasRestantes();
                @endphp

                <div
                    class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                    @if ($item->capa_path)
                        <img src="{{ Storage::disk('public')->url($item->capa_path) }}" alt=""
                            class="h-32 w-full object-cover" />
                    @else
                        <div class="h-32 w-full bg-gradient-to-br from-indigo-500/10 to-blue-500/10"></div>
                    @endif

                    <div class="flex flex-1 flex-col p-5">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <x-badge :color="$item->tipo->color()" :text="$item->tipo->label()" />
                            @if ($licenca->isUsable())
                                <x-badge color="green" text="Disponível" />
                            @elseif ($licenca->isExpired())
                                <x-badge color="red" text="Prazo encerrado" />
                            @else
                                <x-badge :color="$licenca->status->color()" :text="$licenca->status->label()" />
                            @endif
                        </div>

                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->titulo }}</p>

                        @if ($item->resumo)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ $item->resumo }}</p>
                        @endif

                        <div class="mt-3 space-y-1 text-xs text-slate-500 dark:text-slate-400">
                            @if (!$ehPalestra || $item->lessons->isNotEmpty())
                                <p>{{ $item->lessons->count() }}
                                    {{ $item->lessons->count() === 1 ? 'aula' : 'aulas' }}
                                    @if ($item->workload_hours)
                                        · {{ $item->workload_hours }}h
                                    @endif
                                </p>
                            @endif
                            <p>
                                @if ($ehPalestra)
                                    {{ $edicoes }} {{ $edicoes === 1 ? 'edição agendada' : 'edições agendadas' }}
                                @else
                                    {{ $edicoes }} {{ $edicoes === 1 ? 'turma aberta' : 'turmas abertas' }}
                                @endif
                                @if ($restantes !== null)
                                    · {{ $restantes }} restante(s)
                                @endif
                            </p>
                            @if ($licenca->exibir_ate)
                                <p>Disponível até {{ $licenca->exibir_ate->format('d/m/Y') }}</p>
                            @endif
                        </div>

                        <a href="{{ route('admin.catalogo-regional.show', $licenca) }}"
                            class="mt-4 inline-flex justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            {{ $ehPalestra ? 'Ver e agendar' : 'Ver e abrir turma' }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
