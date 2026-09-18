@props([
    'area' => 'aluno',
    'brand' => null,
])

@php
    $area = \App\Support\PwaArea::normalize($area);
    $config = \App\Support\PwaArea::config($area);
    $isPortal = $area === \App\Support\PwaArea::PORTAL;
    $brandName = $brand ?: ($config['short_name'] ?? 'Legiscola');
@endphp

@if ($isPortal)
    {{-- Banner de instalação do portal — destaque visual alinhado à identidade da câmara --}}
    <div
        data-pwa
        data-pwa-area="{{ $area }}"
        data-pwa-title="Leve a Escola Legislativa no celular"
        data-pwa-hint="Instale o app para abrir turmas, eventos e notícias pela tela inicial — rápido e sem digitar o site toda vez."
        data-pwa-hint-ios="No iPhone: toque em Compartilhar (□↑) e depois em “Adicionar à Tela de Início”."
        data-pwa-soft="1"
        x-data="legiscolaPwaInstall"
        x-cloak
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-8 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-6 opacity-0"
        class="fixed inset-x-0 bottom-0 z-[90] px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-5"
        role="dialog"
        aria-label="Instalar aplicativo"
    >
        <div class="relative mx-auto max-w-xl overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-[0_20px_50px_-20px_rgba(15,23,42,0.45)] ring-1 ring-black/5">
            <div class="absolute inset-x-0 top-0 h-1.5"
                 style="background:linear-gradient(90deg,var(--portal-primary,#2563eb),var(--portal-tertiary,#22d3ee),var(--portal-secondary,#1e3a8a))"></div>
            <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full opacity-20 blur-2xl"
                 style="background:var(--portal-primary,#3b82f6)"></div>

            <div class="relative flex gap-4 p-4 sm:gap-5 sm:p-5">
                <div class="shrink-0">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl shadow-lg sm:h-16 sm:w-16"
                         style="background:linear-gradient(145deg,var(--portal-primary,#3b82f6),var(--portal-secondary,#1e40af))">
                        <img src="{{ asset('img/pwa-icon-192.png') }}" alt="" class="h-10 w-10 rounded-xl object-contain sm:h-11 sm:w-11" width="44" height="44">
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em]"
                       style="color:var(--portal-secondary,#1e3a8a)">Aplicativo · {{ \Illuminate\Support\Str::limit($brandName, 28) }}</p>
                    <p class="mt-1 text-base font-bold leading-snug text-slate-900 sm:text-lg" x-text="title"></p>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600" x-text="hint"></p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="installOrGuide()"
                            class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:opacity-95 active:scale-[0.98] sm:flex-none"
                            style="background:linear-gradient(135deg,var(--portal-primary,#2563eb),var(--portal-secondary,#1e3a8a))"
                        >
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"/>
                            </svg>
                            <span x-text="canPrompt ? 'Instalar o aplicativo' : 'Como instalar'"></span>
                        </button>
                        <button
                            type="button"
                            @click="dismiss()"
                            class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        >
                            Agora não
                        </button>
                    </div>

                    <p x-cloak x-show="showGuide && !canPrompt" x-transition
                       class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-xs leading-relaxed text-slate-600">
                        <span x-show="isIos">Safari → <strong>Compartilhar</strong> → <strong>Adicionar à Tela de Início</strong>.</span>
                        <span x-show="!isIos">No Chrome: menu <strong>⋮</strong> → <strong>Instalar aplicativo</strong> (ou “Adicionar à tela inicial”).</span>
                    </p>
                </div>

                <button type="button" @click="dismiss()"
                        class="absolute right-2 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Fechar aviso">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@else
    {{-- Banner compacto das áreas internas (aluno / professor / diretor) --}}
    <div
        data-pwa
        data-pwa-area="{{ $area }}"
        data-pwa-title="{{ $config['install_title'] }}"
        data-pwa-hint="{{ $config['install_hint'] }}"
        data-pwa-hint-ios="{{ $config['install_hint_ios'] }}"
        x-data="legiscolaPwaInstall"
        x-cloak
        x-show="visible"
        x-transition.opacity
        class="fixed inset-x-0 bottom-0 z-50 px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-4"
        role="dialog"
        aria-label="Instalar aplicativo"
    >
        <div class="mx-auto flex max-w-lg items-start gap-3 rounded-2xl border border-cyan-500/30 bg-slate-900/95 p-4 shadow-2xl shadow-black/40 backdrop-blur dark:border-indigo-400/30">
            <img src="{{ asset('img/pwa-icon-192.png') }}" alt="" class="mt-0.5 h-11 w-11 shrink-0 rounded-xl" width="44" height="44">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-white" x-text="title"></p>
                <p class="mt-1 text-xs leading-relaxed text-slate-400" x-text="hint"></p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        type="button"
                        x-show="canPrompt"
                        @click="install()"
                        class="inline-flex min-h-11 items-center rounded-xl bg-cyan-500 px-3.5 py-2 text-xs font-bold text-slate-950 transition hover:bg-cyan-400"
                    >
                        Instalar
                    </button>
                    <button
                        type="button"
                        @click="dismiss()"
                        class="inline-flex min-h-11 items-center rounded-xl border border-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-300 transition hover:bg-slate-800"
                    >
                        Agora não
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
