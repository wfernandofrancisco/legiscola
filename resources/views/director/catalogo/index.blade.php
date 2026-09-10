<x-layouts.director>
    <x-page-header title="Catálogo"
        subtitle="Cursos e palestras que você produz e libera para as câmaras da sua região."
        :items="[['title' => 'itens', 'value' => $itens->total(), 'color' => 'emerald']]"
        :action-href="route('diretor.catalogo.create')" action-text="Novo item" />

    <form method="GET" action="{{ route('diretor.catalogo.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
        <div class="min-w-[220px] flex-1">
            <x-form.input name="q" label="Buscar pelo título" :value="request('q')" autocomplete="off" />
        </div>
        <div class="w-44">
            <x-form.select name="tipo" label="Tipo" placeholder="Todos" :options="$tipos" :selected="request('tipo')" />
        </div>
        <div class="w-44">
            <x-form.select name="status" label="Situação" placeholder="Todas" :options="$statuses"
                :selected="request('status')" />
        </div>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Filtrar
        </button>
        @if (request()->hasAny(['q', 'tipo', 'status']))
            <a href="{{ route('diretor.catalogo.index') }}"
                class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                Limpar
            </a>
        @endif
    </form>

    @if ($itens->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Seu catálogo está vazio.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Crie um curso ou uma palestra para poder liberar às câmaras da sua região.
            </p>
            <a href="{{ route('diretor.catalogo.create') }}"
                class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Criar o primeiro item
            </a>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($itens as $item)
                <a href="{{ route('diretor.catalogo.show', $item) }}"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-indigo-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-indigo-700">
                    @if ($item->capa_path)
                        <img src="{{ Storage::disk('public')->url($item->capa_path) }}" alt=""
                            class="h-32 w-full object-cover" />
                    @else
                        <div class="h-32 w-full bg-gradient-to-br from-indigo-500/10 to-blue-500/10"></div>
                    @endif

                    <div class="flex flex-1 flex-col p-5">
                        <div class="mb-2 flex items-center gap-2">
                            <x-badge :color="$item->tipo->color()" :text="$item->tipo->label()" />
                            <x-badge :color="$item->status->color()" :text="$item->status->label()" />
                        </div>

                        <p
                            class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                            {{ $item->titulo }}
                        </p>

                        @if ($item->resumo)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ $item->resumo }}</p>
                        @endif

                        <div
                            class="mt-auto flex items-center gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            <span>{{ $item->lessons_count }}
                                {{ $item->lessons_count === 1 ? 'aula' : 'aulas' }}</span>
                            <span>·</span>
                            <span>{{ $item->licenses_count }}
                                {{ $item->licenses_count === 1 ? 'câmara' : 'câmaras' }}</span>
                            @if ($item->workload_hours)
                                <span>·</span>
                                <span>{{ $item->workload_hours }}h</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $itens->links() }}
        </div>
    @endif
</x-layouts.director>
