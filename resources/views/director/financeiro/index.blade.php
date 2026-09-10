@php
    $brl = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
@endphp

<x-layouts.director>
    <x-page-header title="Financeiro" subtitle="Recebíveis das licenças que você emitiu."
        :items="[['title' => 'licenças com valor', 'value' => $resumo['licencas'], 'color' => 'emerald']]" />

    <form method="GET" action="{{ route('diretor.financeiro.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
        <div class="w-40">
            <x-form.select name="ano" label="Ano" :options="collect($anos)->mapWithKeys(fn($a) => [$a => $a])->all()"
                :selected="$ano" />
        </div>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Aplicar
        </button>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Contratado</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-slate-900 dark:text-white">
                {{ $brl($resumo['contratado']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800/50 dark:bg-emerald-950/30">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">Recebido</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-emerald-900 dark:text-emerald-200">
                {{ $brl($resumo['recebido']) }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/50 dark:bg-blue-950/30">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">Em aberto</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-blue-900 dark:text-blue-200">
                {{ $brl($resumo['em_aberto']) }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800/50 dark:bg-red-950/30">
            <p class="text-xs font-medium uppercase tracking-wide text-red-700 dark:text-red-400">Vencido</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-red-900 dark:text-red-200">
                {{ $brl($resumo['vencido']) }}</p>
        </div>
    </div>

    @if ($licencas->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhum valor lançado em {{ $ano }}.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Preencha o valor no bloco financeiro de uma licença para ela entrar neste painel.
            </p>
        </div>
    @else
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <section>
                <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Por UF</h2>
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Estado</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Contratado</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Recebido</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Em aberto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($porUf as $linha)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">
                                        {{ $linha['estado'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-300">
                                        {{ $brl($linha['contratado']) }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-emerald-600 dark:text-emerald-400">
                                        {{ $brl($linha['recebido']) }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-blue-600 dark:text-blue-400">
                                        {{ $brl($linha['em_aberto']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Por mês de vencimento</h2>
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Mês</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Contratado</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Recebido</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Em aberto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($porMes as $linha)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">
                                        {{ $linha['rotulo'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-300">
                                        {{ $brl($linha['contratado']) }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-emerald-600 dark:text-emerald-400">
                                        {{ $brl($linha['recebido']) }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-blue-600 dark:text-blue-400">
                                        {{ $brl($linha['em_aberto']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="mb-6">
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Por câmara</h2>
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Câmara</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Licenças</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Contratado</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Recebido</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Em aberto</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Vencido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($porCamara as $linha)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('diretor.clientes.show', $linha['tenant']) }}"
                                        class="text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                        {{ $linha['tenant']->display_name }}
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $linha['tenant']->estado }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-300">
                                    {{ $linha['licencas'] }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-300">
                                    {{ $brl($linha['contratado']) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-emerald-600 dark:text-emerald-400">
                                    {{ $brl($linha['recebido']) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-blue-600 dark:text-blue-400">
                                    {{ $brl($linha['em_aberto']) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-red-600 dark:text-red-400">
                                    {{ $brl($linha['vencido']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Licenças</h2>
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Item / câmara</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Vencimento</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Pagamento</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Nota fiscal</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Valor</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($licencas as $licenca)
                                @php
                                    $vencida = $licenca->pagamento_status->isOpen()
                                        && $licenca->vencimento_em
                                        && $licenca->vencimento_em->isBefore(now()->startOfDay());
                                @endphp
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ $licenca->catalogItem->titulo }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $licenca->tenant->display_name }} / {{ $licenca->tenant->estado }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $licenca->vencimento_em?->format('d/m/Y') ?? '—' }}
                                        @if ($vencida)
                                            <span class="ml-1 text-xs font-semibold text-red-500">vencido</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-badge :color="$licenca->pagamento_status->color()"
                                            :text="$licenca->pagamento_status->label()" />
                                        @if ($licenca->pago_em)
                                            <p class="mt-1 text-[11px] text-slate-400">
                                                em {{ $licenca->pago_em->format('d/m/Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $licenca->nota_fiscal_numero ?: '—' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                                        {{ $brl($licenca->valor) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('diretor.licencas.edit', $licenca) }}"
                                            class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Lançar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</x-layouts.director>
