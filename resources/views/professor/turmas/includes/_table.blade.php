<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left rounded-tl-lg">Turma</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Curso</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-center">Matrículas</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-center">Status</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($turmas as $courseClass)
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
                        @if($courseClass->max_seats !== null)
                            <p class="text-xs text-gray-500 dark:text-gray-400">Vagas: {{ $courseClass->max_seats }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-700 dark:text-gray-300">{{ $courseClass->course?->name ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">
                        {{ (int) ($courseClass->matriculas_count ?? 0) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge :color="$statusMap['color']" :text="$statusMap['label']" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="gray" title="Abrir turma" type="link"
                                href="{{ route('professor.turmas.show', $courseClass) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="green" title="Ficha de presença" type="link"
                                href="{{ route('professor.turmas.ficha-presenca', ['turma' => $courseClass, 'date' => now()->toDateString(), 'tab' => 'chamadas']) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9m-6-4h6m0 0v6m0-6L10 14" />
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
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhuma turma atribuída</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500">Peça à coordenação para vincular seu cadastro à turma.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($turmas->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $turmas->links() }}
        </div>
    @endif
</div>
