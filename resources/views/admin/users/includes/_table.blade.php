<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <x-table.sort-th column="name" label="Usuário" class="rounded-tl-lg" />
                <x-table.sort-th column="email" label="E-mail" />
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Telefone</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Tipo</th>
                <x-table.sort-th column="status" label="Status" align="center" />
                <x-table.sort-th column="created_at" label="Criado em" align="center" />
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center shrink-0 font-semibold text-sm text-indigo-600 dark:text-indigo-400">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-600 dark:text-gray-400 text-xs truncate">{{ $user->email }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if ($user->phone)
                            <span class="text-gray-600 dark:text-gray-400">{{ $user->formatted_phone }}</span>
                        @else
                            <span class="text-gray-400 dark:text-gray-500 italic">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <x-badge :color="$user->user_type_color" :text="$user->user_type_label" />
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge :color="$user->status_color" :text="$user->status_label" />
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-600 dark:text-gray-400 text-xs">
                            {{ $user->created_at->format('d/m/Y') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('view', $user)
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.users.edit', $user) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>
                            @endcan

                            @can('delete', $user)
                            <form id="destroy-form-{{ $user->id }}" method="POST"
                                action="{{ route('admin.users.destroy', $user) }}" style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Usuário', 'Esta ação é irreversível. O usuário {{ $user->name }} será removido permanentemente.', 'destroy-form-{{ $user->id }}')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </x-table-action-button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <svg class="mx-auto w-8 h-8 text-gray-300 dark:text-gray-600 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhum usuário encontrado</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Ajuste seus filtros e tente novamente</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    @if ($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $users->links() }}
        </div>
    @endif
</div>
