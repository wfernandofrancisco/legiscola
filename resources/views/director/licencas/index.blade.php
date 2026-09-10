<x-layouts.director>
    <x-page-header title="Licenças" subtitle="O que cada câmara da sua região pode usar do seu catálogo."
        :items="[['title' => 'licenças', 'value' => $licencas->total(), 'color' => 'emerald']]"
        :action-href="route('diretor.licencas.create')" action-text="Liberar item" />

    <form method="GET" action="{{ route('diretor.licencas.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
        <div class="w-52">
            <x-form.select name="status" label="Situação" placeholder="Todas" :options="$statuses"
                :selected="request('status')" />
        </div>
        <div class="min-w-[220px] flex-1">
            <x-form.select name="tenant" label="Câmara" placeholder="Todas"
                :options="$camaras->mapWithKeys(fn($c) => [$c->id => $c->display_name . ' / ' . $c->estado])->all()"
                :selected="request('tenant')" />
        </div>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Filtrar
        </button>
        @if (request()->hasAny(['status', 'tenant']))
            <a href="{{ route('diretor.licencas.index') }}"
                class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                Limpar
            </a>
        @endif
    </form>

    @if ($licencas->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhuma licença emitida ainda.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Libere um item publicado do seu catálogo para uma câmara da sua região.
            </p>
        </div>
    @else
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Item</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Câmara</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Situação</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Limites</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Financeiro</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($licencas as $licenca)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                        {{ $licenca->catalogItem->titulo }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $licenca->catalogItem->tipo->label() }}
                                        @if ($licenca->professor_nome)
                                            · {{ $licenca->professor_nome }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-slate-800 dark:text-slate-200">
                                        {{ $licenca->tenant->display_name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $licenca->tenant->cidade ?: '—' }} / {{ $licenca->tenant->estado }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <x-badge :color="$licenca->status->color()" :text="$licenca->status->label()" />
                                    @if ($licenca->isExpired())
                                        <p class="mt-1 text-[11px] font-medium text-red-500">Prazo vencido</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                    <p>{{ $licenca->max_turmas ? $licenca->max_turmas . ' turma(s)' : 'Turmas ilimitadas' }}
                                    </p>
                                    <p>{{ $licenca->exibir_ate ? 'Até ' . $licenca->exibir_ate->format('d/m/Y') : 'Sem prazo' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($licenca->valor !== null)
                                        <p class="text-sm font-medium tabular-nums text-slate-800 dark:text-slate-200">
                                            R$ {{ number_format((float) $licenca->valor, 2, ',', '.') }}</p>
                                        <x-badge :color="$licenca->pagamento_status->color()"
                                            :text="$licenca->pagamento_status->label()" />
                                    @else
                                        <span class="text-xs text-slate-400">Sem valor</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('diretor.licencas.edit', $licenca) }}"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40">
                                            Editar
                                        </a>
                                        <form method="POST"
                                            action="{{ route('diretor.licencas.destroy', $licenca) }}"
                                            onsubmit="return confirm('Remover esta licença? A câmara perde o acesso ao conteúdo.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $licencas->links() }}
        </div>
    @endif
</x-layouts.director>
