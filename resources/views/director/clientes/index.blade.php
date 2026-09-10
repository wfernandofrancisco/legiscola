<x-layouts.director>
    <x-page-header title="Câmaras" subtitle="Todas as câmaras sob sua direção, com os números de cada uma."
        :items="[['title' => 'listadas', 'value' => $rows->count(), 'color' => 'sky']]" />

    <form method="GET" action="{{ route('diretor.clientes.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
        <div class="min-w-[220px] flex-1">
            <x-form.input name="q" label="Buscar por nome ou cidade" :value="$busca" autocomplete="off" />
        </div>

        @if (count($ufs) > 1)
            <div class="w-40">
                <x-form.select name="uf" label="UF" placeholder="Todas"
                    :options="collect($ufs)->mapWithKeys(fn($uf) => [$uf => $uf . ' — ' . \App\Support\BrazilianStates::name($uf)])->all()"
                    :selected="$ufSelecionada" />
            </div>
        @endif

        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Filtrar
        </button>

        @if ($busca !== '' || $ufSelecionada !== '')
            <a href="{{ route('diretor.clientes.index') }}"
                class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                Limpar
            </a>
        @endif
    </form>

    @if ($rows->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhuma câmara encontrada.</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($rows as $row)
                <a href="{{ route('diretor.clientes.show', $row['tenant']) }}"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-indigo-700">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-semibold text-slate-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                                {{ $row['tenant']->display_name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $row['tenant']->cidade ?: '—' }} / {{ $row['tenant']->estado }}
                            </p>
                        </div>
                        <x-badge :color="$row['tenant']->status === 'ativo' ? 'green' : 'gray'"
                            :text="ucfirst($row['tenant']->status)" />
                    </div>

                    <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div>
                            <dt class="text-[11px] uppercase tracking-wide text-slate-400">Cursos</dt>
                            <dd class="text-lg font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $row['cursos'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wide text-slate-400">Alunos</dt>
                            <dd class="text-lg font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $row['alunos'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wide text-slate-400">Matrículas</dt>
                            <dd class="text-lg font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $row['matriculas'] }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-1.5 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <span
                            class="rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                            {{ $row['turmas_inscricao'] }} em inscrição
                        </span>
                        <span
                            class="rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                            {{ $row['turmas_em_andamento'] }} em andamento
                        </span>
                        <span
                            class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ $row['turmas_concluidas'] }} concluídas
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.director>
