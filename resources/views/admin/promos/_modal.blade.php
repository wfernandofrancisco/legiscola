@if (! request()->routeIs('admin.promos.show') && ($avisosRegionais ?? collect())->isNotEmpty())
    @php
        $avisosLista = $avisosRegionais->values();
        $avisosTotal = $avisosLista->count();
    @endphp

    <div id="admin-promo-modal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
        role="dialog" aria-modal="true" aria-labelledby="admin-promo-modal-title" data-promo-count="{{ $avisosTotal }}">
        <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-[3px]" data-promo-backdrop></div>

        <div class="relative flex w-full max-w-xl items-center gap-2 sm:gap-3">
            @if ($avisosTotal > 1)
                <button type="button" data-promo-prev
                    class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 sm:flex"
                    aria-label="Aviso anterior">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @endif

            <div
                class="relative w-full overflow-hidden rounded-[1.75rem] border border-amber-200/80 bg-[#fffaf3] shadow-[0_24px_80px_rgba(15,23,42,.45)] dark:border-amber-700/40 dark:bg-slate-900">
                <button type="button" data-promo-close
                    class="absolute right-3 top-3 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white transition hover:bg-black/55 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    aria-label="Fechar aviso">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="overflow-hidden">
                    <div data-promo-track class="flex transition-transform duration-300 ease-out"
                        style="transform: translateX(0%)">
                        @foreach ($avisosLista as $index => $aviso)
                            @php
                                $item = $aviso->catalogItem;
                                $capaUrl = $aviso->coverUrl();
                            @endphp
                            <article class="w-full shrink-0" data-promo-slide
                                aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                                @if ($capaUrl)
                                    <div class="relative h-44 w-full overflow-hidden sm:h-52">
                                        <img src="{{ $capaUrl }}" alt="" class="h-full w-full object-cover">
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-[#fffaf3] via-[#fffaf3]/10 to-black/25 dark:from-slate-900">
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="h-2 w-full bg-gradient-to-r from-amber-500 via-orange-400 to-amber-600">
                                    </div>
                                @endif

                                <div class="px-5 pb-5 pt-4 sm:px-7 sm:pb-7 sm:pt-5">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">
                                        Comunicado regional
                                        @if ($avisosTotal > 1)
                                            <span class="tracking-normal text-slate-400"> · {{ $index + 1 }}/{{ $avisosTotal }}</span>
                                        @endif
                                    </p>
                                    <h2 @if ($index === 0) id="admin-promo-modal-title" @endif
                                        class="mt-2 font-serif text-3xl leading-tight tracking-tight text-slate-900 dark:text-white">
                                        {{ $aviso->titulo }}
                                    </h2>

                                    @if ($aviso->desconto_percentual)
                                        <p class="mt-3 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                                            {{ $aviso->desconto_percentual }}% de desconto</p>
                                    @endif

                                    @if ($aviso->mensagem)
                                        <p class="mt-4 whitespace-pre-line text-base leading-8 text-pretty text-slate-700 dark:text-slate-200">
                                            {{ $aviso->mensagem }}</p>
                                    @endif

                                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $item?->titulo }}
                                        @if ($aviso->preco_por)
                                            · <strong class="text-emerald-700 dark:text-emerald-300">R$
                                                {{ number_format((float) $aviso->preco_por, 2, ',', '.') }}</strong>
                                            @if ($aviso->preco_de)
                                                <span class="line-through">R$
                                                    {{ number_format((float) $aviso->preco_de, 2, ',', '.') }}</span>
                                            @endif
                                        @endif
                                        @if ($aviso->termina_em)
                                            · até {{ $aviso->termina_em->format('d/m/Y') }}
                                        @endif
                                    </p>

                                    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                                        <a href="{{ route('admin.promos.show', $aviso) }}"
                                            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                            Ver curso e aulas
                                        </a>
                                        <form method="POST" action="{{ route('admin.promos.dismiss', $aviso) }}"
                                            class="sm:w-auto" data-promo-dismiss>
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                                                Fechar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                @if ($avisosTotal > 1)
                    <div class="flex items-center justify-center gap-3 border-t border-amber-100 px-4 py-3 dark:border-slate-800 sm:hidden">
                        <button type="button" data-promo-prev
                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-slate-300 text-slate-700 dark:border-slate-600 dark:text-slate-200"
                            aria-label="Aviso anterior">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div data-promo-dots class="flex items-center gap-1.5"></div>
                        <button type="button" data-promo-next
                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-slate-300 text-slate-700 dark:border-slate-600 dark:text-slate-200"
                            aria-label="Próximo aviso">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <div data-promo-dots class="hidden items-center justify-center gap-1.5 pb-4 sm:flex"></div>
                @endif
            </div>

            @if ($avisosTotal > 1)
                <button type="button" data-promo-next
                    class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 sm:flex"
                    aria-label="Próximo aviso">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <script>
        (function() {
            var root = document.getElementById('admin-promo-modal');
            if (!root) return;

            document.body.classList.add('overflow-hidden');

            var track = root.querySelector('[data-promo-track]');
            var slides = Array.prototype.slice.call(root.querySelectorAll('[data-promo-slide]'));
            var total = slides.length;
            var index = 0;
            var dotsWraps = Array.prototype.slice.call(root.querySelectorAll('[data-promo-dots]'));

            function go(next) {
                if (!total) return;
                index = (next + total) % total;
                if (track) track.style.transform = 'translateX(' + (-index * 100) + '%)';
                slides.forEach(function(slide, i) {
                    slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
                });
                dotsWraps.forEach(function(wrap) {
                    Array.prototype.forEach.call(wrap.children, function(dot, i) {
                        dot.setAttribute('aria-current', i === index ? 'true' : 'false');
                        dot.classList.toggle('bg-amber-600', i === index);
                        dot.classList.toggle('bg-slate-300', i !== index);
                    });
                });
            }

            if (total > 1) {
                dotsWraps.forEach(function(wrap) {
                    slides.forEach(function(_, i) {
                        var dot = document.createElement('button');
                        dot.type = 'button';
                        dot.className = 'h-2.5 w-2.5 rounded-full bg-slate-300 transition';
                        dot.setAttribute('aria-label', 'Ir para o aviso ' + (i + 1));
                        dot.addEventListener('click', function() { go(i); });
                        wrap.appendChild(dot);
                    });
                });
            }

            root.querySelectorAll('[data-promo-prev]').forEach(function(btn) {
                btn.addEventListener('click', function() { go(index - 1); });
            });
            root.querySelectorAll('[data-promo-next]').forEach(function(btn) {
                btn.addEventListener('click', function() { go(index + 1); });
            });

            function hideModal() {
                root.classList.add('hidden');
                root.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            function dismissCurrent() {
                var forms = root.querySelectorAll('[data-promo-dismiss]');
                if (forms[index]) {
                    forms[index].submit();
                    return;
                }
                hideModal();
            }

            var closeBtn = root.querySelector('[data-promo-close]');
            if (closeBtn) closeBtn.addEventListener('click', dismissCurrent);

            document.addEventListener('keydown', function(event) {
                if (root.classList.contains('hidden')) return;
                if (event.key === 'Escape') dismissCurrent();
                if (event.key === 'ArrowLeft') go(index - 1);
                if (event.key === 'ArrowRight') go(index + 1);
            });

            go(0);
        })();
    </script>
@endif
