<x-layouts.director>
    <x-page-header :title="'Contatos — '.$promo->titulo"
        subtitle="Pessoas das câmaras que pediram para falar com você sobre este aviso."
        :action-href="route('diretor.promos.index')" action-text="Voltar" />

    @if ($contatos->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhum contato ainda.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Quando um admin clicar em “Falar com diretor regional”, a mensagem aparece aqui.
            </p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Câmara</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">WhatsApp</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Interesse</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Quando</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($contatos as $contato)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">
                                    {{ $contato->tenant?->display_name }}
                                    @if ($contato->tenant?->estado)
                                        <span class="block text-xs text-slate-500">{{ $contato->tenant->estado }}{{ $contato->tenant->cidade ? ' · '.$contato->tenant->cidade : '' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $contato->nome }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ $contato->whatsappLink() }}" target="_blank" rel="noopener"
                                        class="font-semibold text-emerald-700 hover:underline dark:text-emerald-400">
                                        {{ $contato->whatsapp }}
                                    </a>
                                </td>
                                <td class="max-w-sm px-4 py-3 text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $contato->interesse }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500">{{ $contato->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($contatos->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-700">{{ $contatos->links() }}</div>
            @endif
        </div>
    @endif
</x-layouts.director>
