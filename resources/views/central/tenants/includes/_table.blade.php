<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <x-table.sort-th column="name" label="Tenant" class="rounded-tl-lg" />
                <x-table.sort-th column="slug" label="Slug" />
                <x-table.sort-th column="domain" label="Domínio" />
                <x-table.sort-th column="cnpj" label="CNPJ" />
                <x-table.sort-th column="users_count" label="Usuários" align="center" />
                <x-table.sort-th column="status" label="Status" align="center" />
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($tenants as $tenant)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 ">
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $tenant->name }}</p>
                                @if ($tenant->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $tenant->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm text-gray-600 dark:text-gray-400">{{ $tenant->slug }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if ($tenant->domain)
                            <span class="text-gray-600 dark:text-gray-400">{{ $tenant->domain }}</span>
                        @else
                            <span class="text-gray-400 dark:text-gray-500 italic">Não definido</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $tenant->cnpj ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 text-sm font-semibold text-purple-700 dark:text-purple-300">
                            {{ $tenant->users_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($tenant->status === 'ativo')
                            <x-badge color="green" text="Ativo" />
                        @elseif($tenant->status === 'inativo')
                            <x-badge color="gray" text="Inativo" />
                        @elseif($tenant->status === 'suspenso')
                            <x-badge color="red" text="Suspenso" />
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="cyan" title="Ver detalhes"
                                onclick="openTenantModal('{{ route('central.tenants.show', $tenant) }}')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('central.tenants.edit', $tenant) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="purple" title="Usuários" type="link"
                                href="{{ route('central.tenants.users.index', $tenant) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-form-{{ $tenant->id }}" method="POST"
                                action="{{ route('central.tenants.destroy', $tenant) }}" style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Tenant', 'Esta ação é irreversível. Todos os dados de {{ $tenant->name }} serão removidos permanentemente.', 'destroy-form-{{ $tenant->id }}')">
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
                    <td colspan="7" class="px-6 py-12 text-center">
                        <svg class="mx-auto w-8 h-8 text-gray-300 dark:text-gray-600 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhum tenant encontrado</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Comece criando seu primeiro tenant
                        </p>
                        <a href="{{ route('central.tenants.create') }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Novo Tenant
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    @if ($tenants->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $tenants->links() }}
        </div>
    @endif
</div>
