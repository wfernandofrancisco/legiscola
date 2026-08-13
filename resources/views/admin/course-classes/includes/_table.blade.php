<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left rounded-tl-lg">Turma</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Curso</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Período de inscrição</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-center">Status</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($courseClasses as $courseClass)
                @php
                    $statusMap = [
                        'cadastrado' => ['label' => 'Cadastrado', 'color' => 'gray'],
                        'inscricao' => ['label' => 'Inscrição', 'color' => 'blue'],
                        'em_andamento' => ['label' => 'Em andamento', 'color' => 'yellow'],
                        'concluido' => ['label' => 'Concluído', 'color' => 'green'],
                        'cancelado' => ['label' => 'Cancelado', 'color' => 'red'],
                    ][$courseClass->status] ?? ['label' => ucfirst((string) $courseClass->status), 'color' => 'gray'];
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $courseClass->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Vagas: {{ $courseClass->max_seats }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-700 dark:text-gray-300">{{ $courseClass->course?->name ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ optional($courseClass->enrollment_start)->format('d/m/Y H:i') ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            até {{ optional($courseClass->enrollment_end)->format('d/m/Y H:i') ?? '—' }}
                        </p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge :color="$statusMap['color']" :text="$statusMap['label']" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="gray" title="Triagem de alunos" type="link"
                                href="{{ route('admin.turmas.show', $courseClass) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405M19 17l-1.405 1.405M4 7h16M4 12h10M4 17h6" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="green" title="Ficha de presença" type="link"
                                href="{{ route('admin.turmas.ficha-presenca', ['turma' => $courseClass, 'date' => now()->toDateString(), 'tab' => 'chamadas']) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9m-6-4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.turmas.edit', $courseClass) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-course-class-{{ $courseClass->id }}" method="POST"
                                action="{{ route('admin.turmas.destroy', $courseClass) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Turma', 'Confirma exclusão da turma {{ $courseClass->name }}?', 'destroy-course-class-{{ $courseClass->id }}')">
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
                                d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M8 11h8M8 15h5" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhuma turma encontrada</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500">Ajuste seus filtros e tente novamente</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($courseClasses->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $courseClasses->links() }}
        </div>
    @endif
</div>
