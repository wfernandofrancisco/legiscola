<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Disciplina</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Curso</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Ordem</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($curricula as $curriculum)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $curriculum->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $curriculum->workload_hours }}h</p>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $curriculum->course?->name }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $curriculum->position }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.grades-curriculares.edit', $curriculum) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>
                            <form id="destroy-curriculum-{{ $curriculum->id }}" method="POST"
                                action="{{ route('admin.grades-curriculares.destroy', $curriculum) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Disciplina', 'Confirma exclusão de {{ $curriculum->name }}?', 'destroy-curriculum-{{ $curriculum->id }}')">
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
                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Nenhuma disciplina
                        encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($curricula->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $curricula->links() }}
        </div>
    @endif
</div>
