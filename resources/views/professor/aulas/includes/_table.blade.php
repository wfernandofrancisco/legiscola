<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300 rounded-tl-lg">Aula</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Turma</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Data / Horário</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Modalidade</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($classLessons as $classLesson)
                @php
                    $modeMap = $classLesson->is_online
                        ? ['label' => 'Online', 'color' => 'blue']
                        : ['label' => 'Presencial', 'color' => 'green'];
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $classLesson->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">ID #{{ $classLesson->id }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-700 dark:text-gray-300">{{ $classLesson->courseClass?->name ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-700 dark:text-gray-300">{{ $classLesson->date?->format('d/m/Y') ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $classLesson->start_time ? substr((string) $classLesson->start_time, 0, 5) : '--:--' }}
                            às
                            {{ $classLesson->end_time ? substr((string) $classLesson->end_time, 0, 5) : '--:--' }}
                        </p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge :color="$modeMap['color']" :text="$modeMap['label']" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('professor.aulas.edit', $classLesson) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-class-lesson-{{ $classLesson->id }}" method="POST"
                                action="{{ route('professor.aulas.destroy', $classLesson) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>

                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Aula', 'Confirma exclusão da aula {{ $classLesson->title }}?', 'destroy-class-lesson-{{ $classLesson->id }}')">
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
                    <td colspan="5" class="px-6 py-12 text-center">
                        <svg class="mx-auto w-8 h-8 text-gray-300 dark:text-gray-600 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 4h14a2 2 0 012 2v7H3V6a2 2 0 012-2z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhuma aula encontrada</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500">Ajuste seus filtros e tente novamente</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($classLessons->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $classLessons->links() }}
        </div>
    @endif
</div>
