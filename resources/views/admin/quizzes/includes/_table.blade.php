<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Quiz</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Perguntas</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Turmas</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Status</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($quizzes as $quiz)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $quiz->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Nota minima: {{ number_format((float) $quiz->min_score_to_pass, 2, ',', '.') }}%
                        </p>
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $quiz->questions->count() }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach ($quiz->courseClasses as $courseClass)
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $courseClass->name }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <x-badge :color="$quiz->is_active ? 'green' : 'gray'" :text="$quiz->is_active ? 'Ativo' : 'Inativo'" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="indigo" title="Visualizar" type="link"
                                href="{{ route('admin.quizzes.show', $quiz) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-table-action-button>
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.quizzes.edit', $quiz) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>
                            <x-table-action-button color="emerald" title="Imprimir" type="link"
                                href="{{ route('admin.quizzes.print', $quiz) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-quiz-{{ $quiz->id }}" method="POST"
                                action="{{ route('admin.quizzes.destroy', $quiz) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Quiz', 'Confirma exclusão do quiz {{ $quiz->title }}?', 'destroy-quiz-{{ $quiz->id }}')">
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
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                        Nenhum quiz encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($quizzes->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $quizzes->links() }}
        </div>
    @endif
</div>
