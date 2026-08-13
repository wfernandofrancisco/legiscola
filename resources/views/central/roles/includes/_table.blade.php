<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <x-table.sort-th column="name" label="Nome" class="rounded-tl-lg" />
                <x-table.sort-th column="description" label="Descrição" />
                <th class="px-6 py-3 text-center font-semibold text-gray-900 dark:text-gray-100">Tipo</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-900 dark:text-gray-100">Permissions</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-900 dark:text-gray-100 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($roles as $role)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                        {{ $role->name }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-xs max-w-xs">
                        {{ $role->description ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium
                            {{ $role->type === 'central' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                            {{ ucfirst($role->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 dark:bg-indigo-900 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                            {{ $role->permissions->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('central.roles.edit', $role) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-form-{{ $role->id }}" method="POST"
                                action="{{ route('central.roles.destroy', $role) }}" style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Role', 'Esta ação é irreversível. O role {{ $role->name }} será removido permanentemente.', 'destroy-form-{{ $role->id }}')">
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
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhum role encontrado</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    @if ($roles->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $roles->links() }}
        </div>
    @endif
</div>
