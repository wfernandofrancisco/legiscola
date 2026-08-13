<x-layouts.central>
    <x-slot name="title">{{ $permission->name }}</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    {{-- Header com ações --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $permission->name }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $permission->description ?? 'Sem descrição' }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('central.permissions.edit', $permission) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
            <form action="{{ route('central.permissions.destroy', $permission) }}" method="POST"
                onsubmit="return confirm('Tem certeza que deseja deletar esta permissão?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Deletar
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-3xl space-y-6">
        {{-- Card com dados da permission --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dados da Permission</h3>
                    <div class="space-y-4 pt-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nome
                            </p>
                            <p
                                class="text-base font-mono font-semibold text-gray-900 dark:text-white mt-1 bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded text-sm">
                                {{ $permission->name }}
                            </p>
                        </div>
                        @if ($permission->description)
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Descrição</p>
                                <p class="text-base text-gray-900 dark:text-white mt-1">{{ $permission->description }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Roles que usam essa permission --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Roles que Usam esta Permission</h3>

            @if ($permission->roles->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($permission->roles as $role)
                        <a href="{{ route('central.roles.show', $role) }}"
                            class="rounded-lg border-2 border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-900 p-4 hover:border-blue-400 dark:hover:border-blue-700 transition group">
                            <div class="flex items-start gap-3">
                                <div class="text-blue-500 dark:text-blue-400 mt-1 group-hover:scale-110 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="font-medium text-gray-900 dark:text-white text-sm group-hover:text-blue-600 dark:group-hover:text-blue-300 transition">
                                        {{ $role->name }}
                                    </p>
                                    @if ($role->description)
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $role->description }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div
                    class="rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-8 text-center">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Nenhum role está usando esta permission ainda.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.central>
