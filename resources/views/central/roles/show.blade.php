<x-layouts.central>
    <x-slot name="title">{{ $role->name }}</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    {{-- Header com ações --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $role->name }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $role->description ?? 'Sem descrição' }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('central.roles.edit', $role) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
            <form action="{{ route('central.roles.destroy', $role) }}" method="POST"
                onsubmit="return confirm('Tem certeza que deseja deletar este role?');">
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
        {{-- Card com dados do role --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dados do Role</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nome
                            </p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ $role->name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Descrição</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">
                                {{ $role->description ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tipo
                            </p>
                            <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium
                                    {{ $role->type === 'central' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                    {{ ucfirst($role->type) }}
                                </span>
                            </p>
                        </div>
                </div>
            </div>
        </div>

        {{-- Permissions Cards --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Permissions Vinculadas</h3>

            @if ($role->permissions->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($role->permissions as $permission)
                        <div
                            class="rounded-lg border border-indigo-200 dark:border-gray-700 bg-indigo-50 dark:bg-gray-800 p-4 hover:shadow-md dark:hover:shadow-lg transition">
                            <div class="flex items-start gap-3">
                                <div class="text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                        {{ $permission->name }}
                                    </p>
                                    @if ($permission->description)
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1.5">
                                            {{ $permission->description }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-8 text-center">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Este role não possui permissions vinculadas.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.central>
