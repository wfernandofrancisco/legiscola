<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Institucional</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Eixos</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <p class="text-gray-700 dark:text-gray-300 line-clamp-2">{{ strip_tags((string) $item->institucional) ?: '—' }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $item->eixos->count() }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.sobre-escola.edit', $item) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>
                            <form id="destroy-sobre-escola-{{ $item->id }}" method="POST"
                                action="{{ route('admin.sobre-escola.destroy', $item) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir conteúdo', 'Deseja remover este conteúdo institucional?', 'destroy-sobre-escola-{{ $item->id }}')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </x-table-action-button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                        Nenhum conteúdo cadastrado.
                        <a href="{{ route('admin.sobre-escola.create') }}" class="ml-2 text-indigo-600 dark:text-indigo-300 hover:underline">
                            Criar novo registro
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($items->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $items->links() }}
        </div>
    @endif
</div>
