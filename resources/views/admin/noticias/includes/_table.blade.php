<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300 rounded-tl-lg">Titulo</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Formato</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Publicar em</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Status</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fotos</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Acoes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($noticias as $noticia)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $noticia->titulo }}</p>
                        @if ($noticia->subtitulo)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($noticia->subtitulo, 70) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold
                            {{ $noticia->tipo === 'rapida' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : ($noticia->tipo === 'video' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300') }}">
                            {{ $noticia->tipo_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-600 dark:text-gray-400 text-xs">
                            {{ $noticia->publicar_em?->format('d/m/Y H:i') ?? 'Nao definido' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($noticia->ativo)
                            <x-badge color="green" text="Ativo" />
                        @else
                            <x-badge color="red" text="Inativo" />
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-600 dark:text-gray-400 text-xs">{{ $noticia->fotos->count() }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="cyan" title="Visualizar" type="link"
                                href="{{ route('admin.noticias.show', $noticia) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.noticias.edit', $noticia) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-form-{{ $noticia->id }}" method="POST"
                                action="{{ route('admin.noticias.destroy', $noticia) }}" style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Noticia', 'Esta ação é irreversível. A noticia &quot;{{ $noticia->titulo }}&quot; será removida permanentemente.', 'destroy-form-{{ $noticia->id }}')">
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
                    <td colspan="6" class="px-6 py-12 text-center">
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhuma noticia encontrada</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Crie a primeira publicacao para este tenant</p>
                        <a href="{{ route('admin.noticias.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                            Nova noticia
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($noticias->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $noticias->links() }}
        </div>
    @endif
</div>
