<x-layouts.director>
    <x-page-header :title="$item->titulo"
        :subtitle="$item->resumo ?: $item->tipo->label() . ' do seu catálogo'"
        :items="[
            ['title' => 'aulas', 'value' => $item->lessons->count(), 'color' => 'sky'],
            ['title' => 'câmaras com acesso', 'value' => $item->licenses->count(), 'color' => 'emerald'],
        ]" />

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <x-badge :color="$item->tipo->color()" :text="$item->tipo->label()" />
        <x-badge :color="$item->status->color()" :text="$item->status->label()" />

        @if ($item->workload_hours)
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $item->workload_hours }} horas</span>
        @endif

        <div class="ml-auto flex items-center gap-2">
            @if ($item->isLicensable())
                <a href="{{ route('diretor.licencas.create', ['item' => $item->id]) }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Liberar para uma câmara
                </a>
            @endif

            <a href="{{ route('diretor.catalogo.edit', $item) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Editar item
            </a>

            @if ($item->licenses->isEmpty())
                <form method="POST" action="{{ route('diretor.catalogo.destroy', $item) }}"
                    onsubmit="return confirm('Remover {{ $item->titulo }} do catálogo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                        Remover
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (!$item->isLicensable())
        <div
            class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800/50 dark:bg-amber-950/30 dark:text-amber-300">
            Este item está como <strong>{{ $item->status->label() }}</strong>. Só itens publicados podem ser liberados
            para uma câmara.
        </div>
    @endif

    @if ($item->descricao)
        <div
            class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            {!! nl2br(e($item->descricao)) !!}
        </div>
    @endif

    @if ($item->licenses->isNotEmpty())
        <section class="mb-6">
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Câmaras com acesso</h2>

            <div
                class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-900">
                @foreach ($item->licenses as $license)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ $license->tenant->display_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $license->max_turmas ? $license->max_turmas . ' turma(s)' : 'Turmas ilimitadas' }}
                                ·
                                {{ $license->exibir_ate ? 'até ' . $license->exibir_ate->format('d/m/Y') : 'sem prazo' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :color="$license->status->color()" :text="$license->status->label()" />
                            <a href="{{ route('diretor.licencas.edit', $license) }}"
                                class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Editar
                                licença</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mb-6">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Aulas</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                As câmaras recebem esta estrutura; o vídeo e o material continuam vindo daqui.
            </p>
        </div>

        @if ($item->lessons->isEmpty())
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                Nenhuma aula ainda. Adicione a primeira abaixo.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($item->lessons as $lesson)
                    <div x-data="{ editando: false }"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-start gap-4 p-4">
                            <span
                                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ $loop->iteration }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $lesson->titulo }}
                                </p>

                                @if ($lesson->descricao)
                                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $lesson->descricao }}</p>
                                @endif

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                    @if ($lesson->hasVideo())
                                        <span
                                            class="rounded-md bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                            Vídeo{{ $lesson->video_provider ? ' · ' . $lesson->video_provider->label() : '' }}
                                        </span>
                                    @else
                                        <span
                                            class="rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                            Sem vídeo
                                        </span>
                                    @endif

                                    @if ($lesson->hasMaterial())
                                        <span
                                            class="rounded-md bg-blue-50 px-2 py-0.5 font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                            Material
                                        </span>
                                    @endif

                                    @if ($lesson->duracaoFormatada())
                                        <span
                                            class="text-slate-400">{{ $lesson->duracaoFormatada() }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" x-on:click="editando = !editando"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40"
                                    x-text="editando ? 'Fechar' : 'Editar'">Editar</button>

                                <form method="POST"
                                    action="{{ route('diretor.catalogo.aulas.destroy', [$item, $lesson]) }}"
                                    onsubmit="return confirm('Remover a aula {{ $lesson->titulo }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div x-show="editando" x-cloak
                            class="border-t border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/40">
                            <form method="POST"
                                action="{{ route('diretor.catalogo.aulas.update', [$item, $lesson]) }}"
                                enctype="multipart/form-data" class="space-y-5">
                                @csrf
                                @method('PUT')

                                @include('director.catalogo.includes._lesson-fields', ['lesson' => $lesson])

                                <div class="flex justify-end gap-3">
                                    <button type="button" x-on:click="editando = false"
                                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                        Salvar aula
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section x-data="{ aberto: {{ $errors->any() ? 'true' : 'false' }} }"
        class="overflow-hidden rounded-2xl border-2 border-dashed border-indigo-300 bg-indigo-50/70 shadow-sm dark:border-indigo-500/40 dark:bg-indigo-950/30">
        <button type="button" x-on:click="aberto = !aberto"
            class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-indigo-100/80 dark:hover:bg-indigo-900/40">
            <span class="flex items-center gap-3">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-md shadow-indigo-500/30">
                    +
                </span>
                <span>
                    <span class="block text-sm font-bold text-indigo-900 dark:text-indigo-100">Adicionar aula</span>
                    <span class="block text-xs text-indigo-700/80 dark:text-indigo-300/80">
                        Clique para cadastrar vídeo, material e descrição
                    </span>
                </span>
            </span>
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-indigo-600 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white shadow-sm">
                <span x-text="aberto ? 'Fechar' : 'Abrir'"></span>
                <svg class="h-3.5 w-3.5 transition" x-bind:class="aberto && 'rotate-180'" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        <div x-show="aberto" x-cloak class="border-t border-indigo-200 bg-white p-5 dark:border-indigo-800/60 dark:bg-slate-900">
            <form method="POST" action="{{ route('diretor.catalogo.aulas.store', $item) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf

                @include('director.catalogo.includes._lesson-fields', ['lesson' => null])

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-500/25 transition hover:bg-indigo-700">
                        Salvar nova aula
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.director>
