<x-layouts.admin>
    <x-slot name="title">{{ $promo->titulo }}</x-slot>
    <x-breadcrumb :items="[
        ['label' => 'Painel', 'href' => route('admin.dashboard')],
        ['label' => 'Aviso regional'],
    ]" />

    @if ($promo->coverUrl())
        <div class="mb-6 overflow-hidden rounded-2xl border border-amber-200 dark:border-amber-800/50">
            <img src="{{ $promo->coverUrl() }}" alt="" class="h-56 w-full object-cover sm:h-72">
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/80 p-5 dark:border-amber-800/50 dark:bg-amber-950/30 sm:p-6">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge color="yellow" text="Direção regional" />
            @if ($promo->desconto_percentual)
                <x-badge color="green" :text="$promo->desconto_percentual.'% de desconto'" />
            @endif
        </div>
        <h1 class="mt-3 font-serif text-3xl leading-tight font-bold text-slate-900 dark:text-white">{{ $promo->titulo }}</h1>
        @if ($promo->mensagem)
            <p class="mt-3 max-w-3xl whitespace-pre-line text-base leading-8 text-pretty text-slate-700 dark:text-slate-300">{{ $promo->mensagem }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-700 dark:text-slate-300">
            @if ($promo->preco_de || $promo->preco_por)
                <p>
                    @if ($promo->preco_de)
                        <span class="text-slate-500 line-through">R$ {{ number_format((float) $promo->preco_de, 2, ',', '.') }}</span>
                    @endif
                    @if ($promo->preco_por)
                        <strong class="text-emerald-700 dark:text-emerald-300">R$ {{ number_format((float) $promo->preco_por, 2, ',', '.') }}</strong>
                    @endif
                </p>
            @endif
            @if ($promo->termina_em)
                <p>Válido até <strong>{{ $promo->termina_em->format('d/m/Y') }}</strong></p>
            @endif
            <p>Por {{ $promo->director?->name ?? 'direção regional' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ $item->titulo }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $item->tipo->label() }}
                @if ($item->workload_hours)
                    · {{ $item->workload_hours }}h
                @endif
                · {{ $item->lessons->count() }} aula(s)
            </p>
        </div>

        @if ($item->descricao || $item->resumo)
            <div class="border-b border-slate-200 px-5 py-4 text-sm leading-relaxed text-slate-700 dark:border-slate-700 dark:text-slate-300">
                {{ $item->descricao ?: $item->resumo }}
            </div>
        @endif

        <div class="p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Aulas / conteúdo</h3>
            @if ($item->lessons->isEmpty())
                <p class="mt-3 text-sm text-slate-500">A direção regional ainda não cadastrou as aulas deste item.</p>
            @else
                <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($item->lessons as $aula)
                        <li class="flex flex-wrap items-start justify-between gap-3 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $aula->ordem }}. {{ $aula->titulo }}</p>
                                @if ($aula->descricao)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $aula->descricao }}</p>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                @if ($aula->hasVideo())
                                    <x-badge color="blue" text="Vídeo" />
                                @endif
                                @if ($aula->hasMaterial())
                                    <x-badge color="cyan" text="Material" />
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex flex-wrap gap-3 border-t border-slate-200 px-5 py-4 dark:border-slate-700">
            <button type="button" data-promo-contact-open
                class="inline-flex min-h-11 items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                Falar com diretor regional
            </button>
            @if ($licenca)
                <a href="{{ route('admin.catalogo-regional.show', $licenca) }}"
                    class="inline-flex min-h-11 items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Abrir no conteúdo regional
                </a>
            @else
                <a href="{{ route('admin.catalogo-regional.itens.show', $item) }}"
                    class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                    Ver no catálogo
                </a>
            @endif
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Voltar ao painel
            </a>
        </div>
        @unless ($licenca)
            <p class="px-5 pb-4 text-sm text-slate-600 dark:text-slate-400">
                Este conteúdo ainda não foi liberado (licença) para a sua câmara. Fale com a direção regional para contratar.
            </p>
        @endunless
    </div>

    <div id="promo-contact-modal" class="fixed inset-0 z-[80] {{ $errors->hasAny(['nome', 'whatsapp', 'interesse']) ? 'flex' : 'hidden' }} items-center justify-center p-4"
        role="dialog" aria-modal="true" aria-labelledby="promo-contact-title">
        <div class="absolute inset-0 bg-slate-950/70" data-promo-contact-close></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <h2 id="promo-contact-title" class="text-lg font-semibold text-slate-900 dark:text-white">Falar com o diretor regional</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Envie seu WhatsApp e o interesse neste curso. A direção regional vê isso na lista de avisos.
            </p>

            <form method="POST" action="{{ route('admin.promos.contact', $promo) }}" class="mt-5 space-y-4">
                @csrf
                <x-form.input name="nome" label="Nome para contato" required
                    :value="old('nome', auth()->user()->name)" />
                <x-form.input name="whatsapp" label="WhatsApp" required
                    :value="old('whatsapp', auth()->user()->phone)"
                    hint="Com DDD. Ex.: 19 99999-0000" />
                <x-form.textarea name="interesse" label="Descrição do interesse" required rows="5"
                    :value="old('interesse')"
                    hint="O que a câmara precisa, turma, datas, dúvidas." />
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-promo-contact-close
                        class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex min-h-11 items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        Enviar contato
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function() {
            var modal = document.getElementById('promo-contact-modal');
            if (!modal) return;
            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            document.querySelectorAll('[data-promo-contact-open]').forEach(function(btn) {
                btn.addEventListener('click', openModal);
            });
            document.querySelectorAll('[data-promo-contact-close]').forEach(function(btn) {
                btn.addEventListener('click', closeModal);
            });
        })();
    </script>
</x-layouts.admin>
