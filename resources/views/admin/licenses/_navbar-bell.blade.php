@php
    $licencasSino = $licencasPendentes ?? collect();
    $sinoCount = $licencasSino->count();
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" @click="open = !open" title="Conteúdo liberado pela direção"
        class="relative rounded-lg p-2 text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
        :aria-expanded="open.toString()" aria-haspopup="true">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($sinoCount > 0)
            <span
                class="absolute -right-0.5 -top-0.5 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-amber-400 px-1 text-[10px] font-bold leading-none text-slate-900">
                {{ $sinoCount > 9 ? '9+' : $sinoCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak
        class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
        style="display: none;">
        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Liberados pela direção</p>
            <p class="text-xs text-slate-500">Cursos e palestras prontos para agenda.</p>
        </div>

        @if ($sinoCount === 0)
            <p class="px-4 py-6 text-sm text-slate-500">Nenhum curso ou palestra pendente agora.</p>
        @else
            <ul class="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
                @foreach ($licencasSino as $licenca)
                    @php
                        $ehPalestra = $licenca->catalogItem?->isPalestra();
                    @endphp
                    <li>
                        <a href="{{ route('admin.catalogo-regional.show', $licenca) }}"
                            class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $licenca->catalogItem?->titulo }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $ehPalestra ? 'Palestra liberada — agendar evento' : 'Curso liberado — abrir turma' }}
                                @if ($licenca->exibir_ate)
                                    · até {{ $licenca->exibir_ate->format('d/m/Y') }}
                                @endif
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
