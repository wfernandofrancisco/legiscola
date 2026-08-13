@props([
    'name',
    'title' => 'Detalhes',
    'data' => [],
    'show' => false,
    'content' => null
])

<div
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center"
    style="display: none;"
    x-init="$watch('show', value => {
        $el.style.display = value ? 'flex' : 'none';
        if (value) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:click="show = false"
>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden" x-on:click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $title }}
            </h3>
            <button
                type="button"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 max-h-[60vh] overflow-y-auto">
            @if($content)
                {{ $content }}
            @elseif(count($data) > 0)
                <dl class="space-y-3">
                    @foreach($data as $field)
                        <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4 py-2.5
                            {{ !$loop->last ? 'border-b border-gray-50 dark:border-gray-700/50' : '' }}">
                            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider sm:w-1/3 sm:pt-0.5 shrink-0">
                                {{ $field['label'] ?? 'Campo' }}
                            </dt>
                            <dd class="text-sm text-gray-800 dark:text-gray-200 sm:w-2/3">
                                @if(isset($field['badge']))
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $field['badge']['color'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $field['value'] }}
                                    </span>
                                @else
                                    {{ $field['value'] ?? '—' }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Nenhum dado disponível</p>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
            <x-secondary-button x-on:click="$dispatch('close-modal', '{{ $name }}')">
                Fechar
            </x-secondary-button>
        </div>
    </div>
</div>