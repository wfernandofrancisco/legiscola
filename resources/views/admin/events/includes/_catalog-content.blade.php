@php
    $conteudos = $event->catalogContent();
@endphp

@if ($event->isFromCatalog())
    <div class="mt-6 rounded-lg border border-violet-200 bg-violet-50/70 p-6 dark:border-violet-800/60 dark:bg-violet-950/30">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge color="violet" text="Conteúdo regional" />
            @if ($event->palestrante_nome)
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Palestrante: {{ $event->palestrante_nome }}</span>
            @endif
        </div>

        @if ($conteudos->isEmpty())
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                @if ($event->catalogLicense?->isExpired())
                    O prazo desta palestra terminou, então o material regional não aparece mais para os alunos.
                @else
                    A direção regional ainda não anexou vídeo ou material a esta palestra.
                @endif
            </p>
        @else
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                Os inscritos veem este material na página do evento enquanto a palestra estiver liberada.
            </p>

            <ul class="mt-4 divide-y divide-violet-200/70 dark:divide-violet-800/40">
                @foreach ($conteudos as $conteudo)
                    @php
                        $videoUrl = $conteudo->effectiveVideoUrl();
                        $materialUrl = $conteudo->materialDownloadUrl();
                    @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $conteudo->titulo }}</span>
                        <span class="flex flex-wrap gap-2">
                            @if ($videoUrl)
                                <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Ver vídeo
                                </a>
                            @endif
                            @if ($materialUrl)
                                <a href="{{ $materialUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    {{ $conteudo->material_file_name ?: 'Abrir material' }}
                                </a>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($event->catalogLicense?->exibir_ate)
            <p class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                Conteúdo liberado até
                <strong class="text-gray-800 dark:text-gray-200">{{ $event->catalogLicense->exibir_ate->format('d/m/Y') }}</strong>.
            </p>
        @endif
    </div>
@endif
