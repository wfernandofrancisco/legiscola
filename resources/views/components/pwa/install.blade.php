@props([
    'area' => 'aluno',
])

@php
    $area = \App\Support\PwaArea::normalize($area);
    $config = \App\Support\PwaArea::config($area);
@endphp

{{-- Banner de instalação (Android: beforeinstallprompt; iOS: instrução manual). --}}
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
