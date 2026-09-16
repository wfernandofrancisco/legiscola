<x-layouts.director>
    <x-page-header title="Avisos para as câmaras"
        subtitle="Banners de desconto ou novidade que o admin vê no dashboard."
        :items="[['title' => 'avisos', 'value' => $promos->total(), 'color' => 'amber']]"
        :action-href="route('diretor.promos.create')" action-text="Novo aviso" />

    @if ($promos->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhum aviso ainda.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Crie um banner (ex.: curso com desconto) para as câmaras ativas da sua região.
            </p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Aviso</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Curso</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Alcance</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Validade</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($promos as $promo)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $promo->titulo }}</p>
                                    @if ($promo->desconto_percentual)
                                        <p class="text-xs text-amber-600 dark:text-amber-400">{{ $promo->desconto_percentual }}% off</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $promo->catalogItem?->titulo }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                    @if ($promo->isGeral())
                                        Geral (UFs)
                                    @else
                                        {{ $promo->tenants->count() }} câmara(s)
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                    {{ $promo->inicia_em?->format('d/m/Y') ?? '—' }}
                                    →
                                    {{ $promo->termina_em?->format('d/m/Y') ?? 'sem fim' }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-badge :color="$promo->ativo && $promo->isWithinSchedule() ? 'green' : 'gray'"
                                        :text="$promo->ativo && $promo->isWithinSchedule() ? 'Ativo' : ($promo->ativo ? 'Fora do prazo' : 'Inativo')" />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <a href="{{ route('diretor.promos.contatos', $promo) }}"
                                            class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/40">
                                            Contatos{{ $promo->contacts_count ? ' ('.$promo->contacts_count.')' : '' }}
                                        </a>
                                        <a href="{{ route('diretor.promos.edit', $promo) }}"
                                            class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40">Editar</a>
                                        <form method="POST" action="{{ route('diretor.promos.destroy', $promo) }}"
                                            class="inline-flex"
                                            onsubmit="return confirm('Remover este aviso?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($promos->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-700">{{ $promos->links() }}</div>
            @endif
        </div>
    @endif
</x-layouts.director>
