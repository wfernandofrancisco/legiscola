@php
    use App\Support\DirectorContext;

    $ufs = DirectorContext::ufs();

    /*
     * O menu acende conforme as telas vão existindo (Route::has), então adicionar
     * catálogo/licenças/financeiro depois não exige mexer neste arquivo.
     */
    $navItems = [
        [
            'route' => 'diretor.dashboard',
            'active' => 'diretor.dashboard',
            'label' => 'Panorama',
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        ],
        [
            'route' => 'diretor.clientes.index',
            'active' => 'diretor.clientes.*',
            'label' => 'Câmaras',
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        ],
        [
            'route' => 'diretor.catalogo.index',
            'active' => 'diretor.catalogo.*',
            'label' => 'Catálogo',
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        ],
        [
            'route' => 'diretor.licencas.index',
            'active' => 'diretor.licencas.*',
            'label' => 'Licenças',
            'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        ],
        [
            'route' => 'diretor.agenda.index',
            'active' => 'diretor.agenda.*',
            'label' => 'Agenda',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'route' => 'diretor.promos.index',
            'active' => 'diretor.promos.*',
            'label' => 'Avisos',
            'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        ],
        [
            'route' => 'diretor.financeiro.index',
            'active' => 'diretor.financeiro.*',
            'label' => 'Financeiro',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    $navItems = array_values(array_filter($navItems, fn ($item) => \Illuminate\Support\Facades\Route::has($item['route'])));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-pwa.meta area="diretor" title="Diretor" />
    <x-favicon />
    <title>Direção Regional — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    @stack('styles')

    {{-- Aplica tema e tamanho de fonte ANTES da renderização (sem flash) --}}
    <script>
        (function() {
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
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

    <div class="flex h-screen overflow-hidden">

        <aside
            class="w-64 shrink-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700/60 text-gray-700 dark:text-gray-100 flex flex-col transition-colors">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</h1>
                <span class="text-xs text-indigo-500 dark:text-gray-400 uppercase tracking-wider font-medium">Direção
                    Regional</span>

                @if ($ufs)
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($ufs as $uf)
                            <span
                                class="inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-950/50 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-200 dark:ring-indigo-800"
                                title="{{ \App\Support\BrazilianStates::name($uf) }}">{{ $uf }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs($item['active']) ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
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
                        <p class="text-xs text-gray-400">Diretor Regional</p>
                    </div>
                    <form method="POST" action="{{ route('diretor.logout') }}">
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

        <div class="flex-1 flex flex-col overflow-hidden">

            <header class="bg-white dark:bg-gray-800 shadow-sm flex items-center justify-between px-6 py-3 gap-4">
                <div class="flex-1"></div>

                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" id="btn-fs-down" title="Diminuir fonte"
                        class="rounded-lg px-2 py-1.5 text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed transition leading-none">A<span
                            class="text-[9px] align-super">−</span></button>

                    <button type="button" id="btn-fs-up" title="Aumentar fonte"
                        class="rounded-lg px-2 py-1.5 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed transition leading-none">A<span
                            class="text-[9px] align-super">+</span></button>

                    <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-600"></div>

                    <button type="button" id="btn-theme" title="Alternar tema claro/escuro"
                        class="rounded-lg p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg id="icon-sun" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4" />
                            <path
                                d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                        </svg>
                        <svg id="icon-moon" class="h-4 w-4 hidden" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                        </svg>
                    </button>

                    @isset($actions)
                        <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-600"></div>
                        <div class="flex items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>
            </header>

            <main class="flex-1 overflow-y-auto overflow-x-hidden p-6">
                @if (session('success'))
                    <div
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
                    <div
                        class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 dark:border-red-800/50 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 dark:border-red-800/50 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                        <p class="font-semibold">Corrija os campos abaixo:</p>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

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

            var isDark = html.classList.contains('dark');

            function applyTheme(dark) {
                dark ? html.classList.add('dark') : html.classList.remove('dark');
                document.getElementById('icon-sun').classList.toggle('hidden', dark);
                document.getElementById('icon-moon').classList.toggle('hidden', !dark);
                localStorage.setItem('theme', dark ? 'dark' : 'light');
                isDark = dark;
            }

            applyTheme(isDark);

            document.getElementById('btn-theme').addEventListener('click', function() {
                applyTheme(!isDark);
            });

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
    </script>

    @stack('scripts')
    <x-pwa.install area="diretor" />
</body>

</html>
