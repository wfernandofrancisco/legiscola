<x-layouts.admin>
    <x-slot name="title">Cursos externos disponíveis</x-slot>

    @php
        $disponiveis = $licencas->filter(fn ($l) => $l->isUsable());
        $ufLabel = $estado !== '' ? $estado : 'sua região';
        $catalogosVitrine = $catalogos->reject(fn ($item) => $licencasPorItem->has($item->id));
    @endphp

    <x-page-header title="Cursos externos disponíveis"
        :subtitle="'Catálogo da direção regional de '.$ufLabel.' — o que está publicado e o que já foi liberado para a sua câmara.'"
        :items="[
            ['title' => 'no catálogo', 'value' => $catalogos->count(), 'color' => 'sky'],
            ['title' => 'liberados', 'value' => $licencas->count(), 'color' => 'violet'],
            ['title' => 'disponíveis agora', 'value' => $disponiveis->count(), 'color' => 'emerald'],
        ]" />

    <section class="mb-8" aria-labelledby="catalogos-titulo">
        <div class="mb-4">
            <h2 id="catalogos-titulo" class="text-base font-semibold text-slate-900 dark:text-white">
                Catálogo da direção regional ({{ $ufLabel }})
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Cursos e palestras publicados para as câmaras deste estado. Para abrir turma, a direção ainda precisa
                liberar a licença.
            </p>
        </div>

        @if ($catalogosVitrine->isEmpty())
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    @if ($catalogos->isEmpty())
                        Nenhum curso externo publicado para {{ $ufLabel }}.
                    @else
                        Os cursos deste catálogo já foram liberados para a sua câmara.
                    @endif
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    @if ($catalogos->isEmpty())
                        Quando a direção regional publicar um curso ou palestra no catálogo, ele aparece aqui.
                    @else
                        Use a seção abaixo para abrir turma ou agendar palestra.
                    @endif
                </p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($catalogosVitrine as $item)
                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                        @if ($item->capa_path)
                            <img src="{{ Storage::disk('public')->url($item->capa_path) }}" alt=""
                                class="h-32 w-full object-cover" />
                        @else
                            <div class="h-32 w-full bg-gradient-to-br from-sky-500/10 to-indigo-500/10"></div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <x-badge :color="$item->tipo->color()" :text="$item->tipo->label()" />
                                <x-badge color="gray" text="Catálogo" />
                            </div>

                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->titulo }}</p>

                            @if ($item->resumo)
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $item->resumo }}</p>
                            @endif

                            <div class="mt-3 space-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <p>
                                    {{ $item->lessons->count() }}
                                    {{ $item->lessons->count() === 1 ? 'aula' : 'aulas' }}
                                    @if ($item->workload_hours)
                                        · {{ $item->workload_hours }}h
                                    @endif
                                </p>
                                @if ($item->owner?->name)
                                    <p>Por {{ $item->owner->name }}</p>
                                @endif
                                @if ($item->preco_sugerido)
                                    <p>Preço sugerido R$ {{ number_format((float) $item->preco_sugerido, 2, ',', '.') }}
                                    </p>
                                @endif
                            </div>

                            <a href="{{ route('admin.catalogo-regional.itens.show', $item) }}"
                                class="mt-4 inline-flex justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                                Ver catálogo
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    @if ($licencas->isNotEmpty())
        <section aria-labelledby="licencas-titulo">
            <div class="mb-4">
                <h2 id="licencas-titulo" class="text-base font-semibold text-slate-900 dark:text-white">
                    Liberados para a sua câmara
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Itens com licença ativa: dá para abrir turma ou agendar palestra.
                </p>
            </div>

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
        </section>
    @endif
</x-layouts.admin>
