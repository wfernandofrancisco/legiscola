<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Aluno</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">E-mail</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Matrícula</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Profissão</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Escolaridade</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Contato</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">CPF</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Status</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($students as $student)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($student->photo_path)
                                <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto do aluno"
                                    class="w-9 h-9 rounded-lg object-cover shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-semibold">
                                    {{ strtoupper(substr($student->user?->name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $student->user?->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $student->user?->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $student->email }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $student->enrollment_number }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $student->profissao ?: '—' }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $student->escolaridade?->label() ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $student->celular ?: $student->telefone ?: '—' }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $student->cpf ?: '—' }}</td>
                    <td class="px-6 py-4">
                        <x-badge :color="($student->status ?? 'ativo') === 'ativo' ? 'green' : 'gray'" :text="($student->status ?? 'ativo') === 'ativo' ? 'Ativo' : 'Inativo'" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.alunos.edit', $student) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>
                            <form id="destroy-student-{{ $student->id }}" method="POST"
                                action="{{ route('admin.alunos.destroy', $student) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Aluno', 'Confirma exclusão do aluno {{ $student->user?->name }}?', 'destroy-student-{{ $student->id }}')">
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
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Nenhum aluno
                        encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($students->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $students->links() }}
        </div>
    @endif
</div>
