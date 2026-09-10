<x-layouts.central>
    <x-slot name="title">Diretores regionais</x-slot>

    <x-breadcrumb />

    <x-page-header title="Diretores regionais"
        subtitle="Camada entre a Central e os clientes: cada diretor responde por uma ou mais UFs."
        :items="[['title' => 'diretores', 'value' => $directors->total(), 'color' => 'emerald']]"
        :action-href="route('central.directors.create')" action-text="Novo diretor" />

    @if ($directors->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhum diretor regional cadastrado.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Crie um diretor e escolha as UFs pelas quais ele responde.
            </p>
        </div>
    @else
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Diretor</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Abrangência</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Clientes alcançados</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Situação</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach ($directors as $director)
                        @php
                            $ufs = $director->directorUfs->pluck('uf')->all();
                            $alcance = collect($ufs)->sum(fn($uf) => (int) ($tenantsPorUf[$uf] ?? 0));
                        @endphp
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $director->name }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $director->email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($ufs as $uf)
                                        <span
                                            class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:ring-indigo-800"
                                            title="{{ \App\Support\BrazilianStates::name($uf) }}">{{ $uf }}</span>
                                    @empty
                                        <span class="text-xs text-red-500">Sem UF</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                {{ $alcance }} {{ $alcance === 1 ? 'câmara' : 'câmaras' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :color="$director->status === 'ativo' ? 'green' : 'gray'"
                                    :text="ucfirst($director->status)" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('central.directors.edit', $director) }}"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('central.directors.destroy', $director) }}"
                                        onsubmit="return confirm('Remover o diretor {{ $director->name }}?');">
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

        <div class="mt-4">
            {{ $directors->links() }}
        </div>
    @endif
</x-layouts.central>
