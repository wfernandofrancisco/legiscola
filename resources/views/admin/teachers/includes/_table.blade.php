<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left rounded-tl-lg">Professor</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Contato</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-left">Especialidades</th>
                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-center">Status</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($teachers as $teacher)
                @php
                    $statusColor = $teacher->status === 'ativo' ? 'green' : 'red';
                    $phone = preg_replace('/\D/', '', (string) $teacher->phone);
                    if (strlen($phone) === 11) {
                        $phoneFormatted = '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
                    } elseif (strlen($phone) === 10) {
                        $phoneFormatted = '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
                    } else {
                        $phoneFormatted = $teacher->phone ?: '—';
                    }
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $teacher->photo_path ? asset('storage/' . $teacher->photo_path) : 'https://placehold.co/64x64/e5e7eb/6b7280?text=Foto' }}"
                                alt="Foto do professor" class="w-9 h-9 rounded-full object-cover shrink-0">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $teacher->full_name ?: ($teacher->user?->name ?? '—') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $teacher->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-600 dark:text-gray-400">{{ $phoneFormatted }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @forelse(array_filter(array_map('trim', explode(',', (string) $teacher->specialities))) as $tag)
                            <span class="inline-flex rounded-full bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 mr-1">{{ $tag }}</span>
                        @empty
                            <span class="text-gray-500">-</span>
                        @endforelse
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge :color="$statusColor" :text="ucfirst((string) $teacher->status)" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.professores.edit', $teacher) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-teacher-{{ $teacher->id }}" method="POST"
                                action="{{ route('admin.professores.destroy', $teacher) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Professor', 'Confirma exclusão do professor {{ $teacher->full_name }}?', 'destroy-teacher-{{ $teacher->id }}')">
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
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhum professor encontrado</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500">Ajuste seus filtros e tente novamente</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($teachers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $teachers->links() }}
        </div>
    @endif
</div>
