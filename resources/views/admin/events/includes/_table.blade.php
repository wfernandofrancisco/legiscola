<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
            <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300 rounded-tl-lg">Evento</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Data</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Cidade</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Inscritos</th>
                <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Inscrição</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($events as $event)
                @php
                    $modeMap = $event->allow_online_registration
                        ? ['label' => 'Online ativa', 'color' => 'green']
                        : ['label' => 'Online desativada', 'color' => 'gray'];
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $event->photo_path ? asset('storage/' . $event->photo_path) : 'https://placehold.co/64x64/e5e7eb/6b7280?text=Evento' }}"
                                alt="Foto do evento"
                                class="h-12 w-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $event->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Vagas: {{ $event->max_seats }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $event->date_time?->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $event->city ?: '—' }}</td>
                    <td class="px-6 py-4 text-center tabular-nums text-gray-700 dark:text-gray-300">
                        {{ $event->enrollments_count }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge :color="$modeMap['color']" :text="$modeMap['label']" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-table-action-button color="cyan" title="Preview"
                                onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'preview-event-{{ $event->id }}' }))">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-table-action-button>

                            <x-table-action-button color="blue" title="Editar" type="link"
                                href="{{ route('admin.eventos.edit', $event) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-table-action-button>

                            <form id="destroy-event-{{ $event->id }}" method="POST"
                                action="{{ route('admin.eventos.destroy', $event) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <x-table-action-button color="red" title="Excluir"
                                onclick="showConfirmModal('Excluir Evento', 'Confirma exclusão do evento {{ $event->title }}?', 'destroy-event-{{ $event->id }}')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </x-table-action-button>
                        </div>

                        <x-modal name="preview-event-{{ $event->id }}" maxWidth="2xl">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $event->title }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $event->date_time?->format('d/m/Y H:i') ?? 'Data não informada' }}
                                        </p>
                                    </div>
                                    <x-badge :color="$modeMap['color']" :text="$modeMap['label']" />
                                </div>

                                <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                    <img src="{{ $event->photo_path ? asset('storage/' . $event->photo_path) : 'https://placehold.co/800x340/e5e7eb/6b7280?text=Sem+foto+do+evento' }}"
                                        alt="Foto do evento {{ $event->title }}" class="h-52 w-full object-cover">
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <p class="text-gray-700 dark:text-gray-300"><span class="font-semibold">Cidade:</span> {{ $event->city ?: '—' }}</p>
                                    <p class="text-gray-700 dark:text-gray-300"><span class="font-semibold">Vagas:</span> {{ $event->max_seats ?? 'Ilimitadas' }}</p>
                                    <p class="text-gray-700 dark:text-gray-300 md:col-span-2"><span class="font-semibold">Endereço:</span>
                                        {{ $event->address ?: '—' }} {{ $event->number ?: '' }} {{ $event->district ? '- '.$event->district : '' }}
                                    </p>
                                </div>

                                @if($event->description)
                                    <div class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Descrição</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $event->description }}</p>
                                    </div>
                                @endif

                                <div class="mt-6 flex justify-end">
                                    <x-secondary-button x-on:click="$dispatch('close')">Fechar</x-secondary-button>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <svg class="mx-auto w-8 h-8 text-gray-300 dark:text-gray-600 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v11a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium mb-1">Nenhum evento encontrado</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500">Ajuste seus filtros e tente novamente</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($events->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $events->links() }}
        </div>
    @endif
</div>
