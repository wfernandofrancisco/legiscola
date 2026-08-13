<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Professor' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Aplica tema e tamanho de fonte ANTES da renderização (sem flash) --}}
    <script>
        (function() {
            // Dark mode
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
            // Tamanho de fonte (altera font-size no :root → escala todo rem)
            var fs = localStorage.getItem('fs');
            var sizes = {
                sm: '14px',
                md: '16px',
                lg: '18px'
            };
            if (fs && sizes[fs]) {
                document.documentElement.style.fontSize = sizes[fs];
            }
        })();
    </script>
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 transition-colors">

    <div class="flex h-screen overflow-hidden ">

        {{-- Sidebar --}}
        <aside
            class="relative w-64 shrink-0 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-100 flex flex-col transition-colors
    before:absolute before:right-0 before:top-[72px] before:bottom-0 before:w-px before:bg-gray-200 dark:before:bg-gray-700">
            <div
                class="relative p-6 border-b border-blue-900 overflow-hidden bg-linear-to-r from-blue-600 via-blue-500 to-blue-700 dark:from-blue-900 dark:via-blue-800 dark:to-blue-950">

                {{-- Ícones flutuantes como marca d'água --}}
                <div
                    class="absolute inset-0 dark:opacity-[0.06] opacity-[0.09] pointer-events-none select-none bottom-0">
                    {{-- Prédios / cidade --}}
                    <svg class="absolute bottom-0 left-0 w-full  dark:text-white text-black " fill="currentColor"
                        viewBox="0 0 400 80" xmlns="http://www.w3.org/2000/svg">
                        <!-- Prédio 1 -->
                        <rect x="10" y="30" width="30" height="50" />
                        <rect x="15" y="20" width="8" height="12" />
                        <rect x="27" y="20" width="8" height="12" />
                        <!-- Prédio 2 -->
                        <rect x="50" y="45" width="20" height="35" />
                        <!-- Prédio 3 -->
                        <rect x="80" y="20" width="40" height="60" />
                        <rect x="85" y="10" width="10" height="12" />
                        <rect x="105" y="10" width="10" height="12" />
                        <!-- Prédio 4 -->
                        <rect x="130" y="35" width="25" height="45" />
                        <!-- Prédio 5 (arranha-céu) -->
                        <rect x="165" y="5" width="35" height="75" />
                        <rect x="178" y="0" width="8" height="8" />
                        <!-- Prédio 6 -->
                        <rect x="210" y="25" width="30" height="55" />
                        <!-- Prédio 7 -->
                        <rect x="250" y="40" width="22" height="40" />
                        <!-- Prédio 8 -->
                        <rect x="282" y="15" width="40" height="65" />
                        <rect x="287" y="5" width="12" height="12" />
                        <!-- Prédio 9 -->
                        <rect x="332" y="30" width="28" height="50" />
                        <!-- Prédio 10 -->
                        <rect x="370" y="45" width="30" height="35" />
                    </svg>

                    {{-- Gráfico de crescimento no canto direito --}}
                    <svg class="absolute top-2 right-2 w-16 h-16 dark:text-white text-black" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 64 64">
                        <polyline points="4,50 16,35 28,40 40,20 52,10 60,15" />
                        <circle cx="52" cy="10" r="3" fill="currentColor" />
                    </svg>

                    {{-- Engrenagem no canto esquerdo superior --}}
                    <svg class="absolute top-2 left-1 w-12 h-12 dark:text-white text-black" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                        <path
                            d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" />
                    </svg>
                </div>

                {{-- Grade sutil --}}
                <div class="absolute inset-0 opacity-[0.04]"
                    style="background-image: linear-gradient(rgba(255,255,255,1) 1px, transparent 1px),
               linear-gradient(90deg, rgba(255,255,255,1) 1px, transparent 1px);
               background-size: 20px 20px;">
                </div>

                {{-- Conteúdo principal --}}
                <div class="relative flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl backdrop-blur-sm border border-white/10">
                        <svg class="w-6 h-6 text-white shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight">
                            {{ config('app.name') }}
                        </h1>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs text-blue-200 uppercase tracking-wider font-medium">
                                {{ auth()->user()->tenant->name ?? 'Escola' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto dark:bg-gray-800">
                <a href="{{ route('professor.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('professor.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Painel
                </a>

                <a href="{{ route('professor.turmas.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('professor.turmas*') && ! request()->routeIs('professor.aulas*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M8 11h8M8 15h5" />
                    </svg>
                    Minhas turmas
                </a>

                <a href="{{ route('professor.aulas.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('professor.aulas*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zM3 9v6l9 5 9-5V9" />
                    </svg>
                    Aulas
                </a>

                <a href="{{ route('professor.quizzes.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('professor.quizzes*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>
                    Quizzes
                </a>

                <a href="{{ route('professor.perfil.edit') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('professor.perfil*') || request()->routeIs('professor.senha*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Meu perfil
                </a>






            </nav>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-sm font-bold text-white shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->isTenantProfessor() ? 'Docente' : 'Gestão' }}</p>
                    </div>
                    <form method="POST" action="{{ route('tenant.logout') }}">
                        @csrf
                        <button type="submit" title="Sair"
                            class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Conteúdo principal --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            <header class="bg-[#1650d9] dark:bg-[#182a67] shadow-sm flex items-center justify-between px-6 py-3 gap-4">
                <div class="flex-1"></div>

                <div class="flex items-center gap-1 shrink-0">

                    {{-- Diminuir fonte --}}
                    <button type="button" id="btn-fs-down" title="Diminuir fonte"
                        class="rounded-lg px-2 py-1.5 text-xs font-bold text-white dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-900 hover:bg-gray-100 dark:hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition leading-none">A<span
                            class="text-[9px] align-super">−</span></button>

                    {{-- Aumentar fonte --}}
                    <button type="button" id="btn-fs-up" title="Aumentar fonte"
                        class="rounded-lg px-2 py-1.5 text-sm font-bold text-white dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-900 hover:bg-gray-100 dark:hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition leading-none">A<span
                            class="text-[9px] align-super">+</span></button>

                    <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-600"></div>

                    {{-- Tema --}}
                    <button type="button" id="btn-theme" title="Alternar tema claro/escuro"
                        class="rounded-lg p-2 text-white dark:text-gray-400 hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-gray-100 dark:hover:text-blue-900 transition">
                        {{-- Sol --}}
                        <svg id="icon-sun" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4" />
                            <path
                                d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                        </svg>
                        {{-- Lua --}}
                        <svg id="icon-moon" class="h-4 w-4 hidden" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                        </svg>
                    </button>

                    <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-600"></div>

                    <a href="{{ route('professor.perfil.edit') }}" title="Editar perfil"
                        class="rounded-lg p-2 text-white dark:text-gray-400 hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-gray-100 dark:hover:text-blue-900 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                    <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-600"></div>

                    <form method="POST" action="{{ route('tenant.logout') }}" class="inline">
                        @csrf
                        <button type="submit" title="Sair"
                            class="rounded-lg p-2 text-white dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-100 hover:text-red-500 dark:hover:text-red-400 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>

                    @isset($actions)
                        <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-600"></div>
                        <div class="flex items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>
            </header>

            <main class="flex-1 overflow-y-auto overflow-x-hidden p-6 text-gray-900 dark:text-gray-100">
                @if (session('success'))
                    <div data-flash-message
                        class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 dark:bg-green-950/30 dark:border-green-800/50 px-4 py-3 text-sm text-green-800 dark:text-green-300">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div data-flash-message
                        class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 dark:border-red-800/50 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Modal global de confirmação --}}
    <x-confirm-modal />

    <script src="{{ asset('js/masks.js') }}"></script>
    <script>
        (function() {
            var html = document.documentElement;
            var SIZES = ['sm', 'md', 'lg'];
            var PX = {
                sm: '14px',
                md: '16px',
                lg: '18px'
            };

            // ── Tema claro / escuro ───────────────────────────────────────
            var isDark = html.classList.contains('dark');

            function applyTheme(dark) {
                dark ? html.classList.add('dark') : html.classList.remove('dark');
                document.getElementById('icon-sun').classList.toggle('hidden', dark);
                document.getElementById('icon-moon').classList.toggle('hidden', !dark);
                localStorage.setItem('theme', dark ? 'dark' : 'light');
                isDark = dark;
            }

            // Ícone inicial (dark pode já ter sido aplicado pelo snippet no <head>)
            applyTheme(isDark);

            document.getElementById('btn-theme').addEventListener('click', function() {
                applyTheme(!isDark);
            });

            // ── Tamanho de fonte ──────────────────────────────────────────
            var saved = localStorage.getItem('fs') || 'md';
            var fsIdx = SIZES.indexOf(saved);
            if (fsIdx === -1) fsIdx = 1;

            function applyFs(idx) {
                fsIdx = idx;
                html.style.fontSize = PX[SIZES[idx]];
                localStorage.setItem('fs', SIZES[idx]);
                document.getElementById('btn-fs-down').disabled = idx === 0;
                document.getElementById('btn-fs-up').disabled = idx === SIZES.length - 1;
            }

            applyFs(fsIdx);

            document.getElementById('btn-fs-down').addEventListener('click', function() {
                if (fsIdx > 0) applyFs(fsIdx - 1);
            });
            document.getElementById('btn-fs-up').addEventListener('click', function() {
                if (fsIdx < SIZES.length - 1) applyFs(fsIdx + 1);
            });
        })();

        (function() {
            var flashMessages = document.querySelectorAll('[data-flash-message]');

            flashMessages.forEach(function(message) {
                message.style.transition =
                    'opacity 300ms ease, transform 300ms ease, max-height 300ms ease, margin 300ms ease, padding 300ms ease';

                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-6px)';
                    message.style.maxHeight = '0';
                    message.style.margin = '0';
                    message.style.paddingTop = '0';
                    message.style.paddingBottom = '0';
                    message.style.overflow = 'hidden';

                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 4000);
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>
