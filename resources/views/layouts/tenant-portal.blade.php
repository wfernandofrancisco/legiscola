<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $tenant->portalBrandTitle())</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    {{-- ═══════════════ HEADER ═══════════════ --}}
    <header id="main-header" class="sticky top-0 z-50 border-b border-slate-100/80 bg-white/90 backdrop-blur-xl transition-all duration-300">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

            {{-- Logo & Brand --}}
            <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-3">
                <div class="overflow-hidden rounded-xl shadow-sm ring-1 ring-slate-200 transition-all duration-200 group-hover:ring-blue-300 group-hover:shadow-md">
                    <img src="{{ asset('img/logo2.png') }}" alt="Logo" class="h-10 w-10 object-cover transition-transform duration-300 group-hover:scale-110">
                </div>
                <div class="hidden sm:block">
                    <p class="text-sm font-extrabold leading-tight text-slate-900">Desenvolve {{ $tenant->cidade }}</p>
                    <p class="text-xs font-medium text-slate-400">Portal Econômico Municipal</p>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden lg:flex items-center gap-0.5">
                @php
                    $navItems = [
                        ['label' => 'Início',    'route' => 'home'],
                        ['label' => 'Mapa',      'route' => 'portal.mapa'],
                        ['label' => 'Empresas',  'route' => 'portal.consultar-empresas'],
                        ['label' => 'Catálogo',  'route' => 'portal.catalogo'],
                        ['label' => 'Números',   'route' => 'portal.contagem'],
                    ];
                @endphp
                @foreach($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="relative px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200
                              {{ $active ? 'text-blue-700 bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                        {{ $item['label'] }}
                        @if($active)
                            <span class="absolute bottom-1 left-1/2 h-0.5 w-4 -translate-x-1/2 rounded-full bg-blue-500"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            {{-- CTA Buttons + Mobile toggle --}}
            <div class="flex items-center gap-1.5 sm:gap-2">
                @guest
                    {{-- Entrar (ghost) --}}
                    <a href="{{ route('tenant.login') }}"
                       class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 active:scale-95">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        Entrar
                    </a>
                    {{-- Sou empresa (outlined) --}}
                    <a href="{{ route('register.responsavel') }}"
                       class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/60 hover:text-blue-700 active:scale-95">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        Sou empresa
                    </a>
                    {{-- Sou Munícipe (filled gradient) --}}
                    <a href="{{ route('register.morador') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-linear-to-r from-blue-600 to-sky-500 px-4 py-2 text-xs font-bold text-white shadow-md transition-all duration-200 hover:from-blue-700 hover:to-sky-600 hover:shadow-lg hover:shadow-blue-200/60 active:scale-95">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        Sou Munícipe
                    </a>
                @else
                    <a href="{{ route(auth()->user()->tenantHomeRouteName()) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-800 shadow-xs transition-all duration-200 hover:bg-slate-50 hover:shadow-sm active:scale-95">
                        Meu painel
                    </a>
                @endguest

                {{-- Mobile hamburger --}}
                <button id="mobile-menu-btn"
                        class="lg:hidden flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition-colors duration-200 hover:bg-slate-100 active:bg-slate-200"
                        aria-label="Abrir menu">
                    <svg id="icon-open" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <svg id="icon-close" class="h-5 w-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile drawer --}}
        <div id="mobile-menu" class="hidden border-t border-slate-100 bg-white/95 backdrop-blur-xl lg:hidden">
            <div class="mx-auto max-w-7xl divide-y divide-slate-100 px-4 sm:px-6">
                <nav class="flex flex-col gap-0.5 py-3">
                    <a href="{{ route('home') }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Início</a>
                    <a href="{{ route('portal.mapa') }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('portal.mapa') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Mapa Interativo</a>
                    <a href="{{ route('portal.consultar-empresas') }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('portal.consultar-empresas') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Consultar Empresas</a>
                    <a href="{{ route('portal.catalogo') }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('portal.catalogo') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Catálogo</a>
                    <a href="{{ route('portal.contagem') }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('portal.contagem') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Painel de Números</a>
                </nav>
                @guest
                <div class="flex flex-col gap-2 py-3">
                    <a href="{{ route('tenant.login') }}"
                       class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-50">Entrar</a>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('register.responsavel') }}"
                           class="flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-semibold text-slate-800 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/60 hover:text-blue-700">Sou empresa</a>
                        <a href="{{ route('register.morador') }}"
                           class="flex items-center justify-center rounded-xl bg-linear-to-r from-blue-600 to-sky-500 px-3 py-2.5 text-xs font-bold text-white shadow-sm transition-all duration-200 hover:from-blue-700 hover:to-sky-600">Sou Munícipe</a>
                    </div>
                </div>
                @endguest
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-900 shadow-xs">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-900 shadow-xs">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="mt-auto bg-slate-900 text-white">
        {{-- Gradient accent bar --}}
        <div class="h-0.75 bg-linear-to-r from-blue-600 via-sky-400 to-cyan-400"></div>
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-4">
                {{-- Brand --}}
                <div>
                    <div class="mb-4 flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-10 w-10 rounded-xl shadow object-cover">
                        <div>
                            <p class="font-bold text-white">{{ $tenant->portalBrandTitle() }}</p>
                            <p class="text-xs text-slate-400">Portal Econômico Municipal</p>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-400">Plataforma oficial de transparência econômica e desenvolvimento do comércio local.</p>
                </div>

                {{-- Navegação --}}
                <div>
                    <h4 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-300">Navegação</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('home') }}" class="transition hover:text-white">Início</a></li>
                        <li><a href="{{ route('portal.mapa') }}" class="transition hover:text-white">Mapa Interativo</a></li>
                        <li><a href="{{ route('portal.consultar-empresas') }}" class="transition hover:text-white">Consultar empresas</a></li>
                        <li><a href="{{ route('portal.catalogo') }}" class="transition hover:text-white">Catálogo de produtos e serviços</a></li>
                        <li><a href="{{ route('portal.contagem') }}" class="transition hover:text-white">Painel de Números</a></li>
                    </ul>
                </div>

                {{-- Para empresas --}}
                <div>
                    <h4 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-300">Para Empresas</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('register.responsavel') }}" class="transition hover:text-white">Cadastrar empresa grátis</a></li>
                        <li><a href="{{ route('tenant.login') }}" class="transition hover:text-white">Acessar painel</a></li>
                        @guest
                        <li><a href="{{ route('register.morador') }}" class="transition-colors duration-200 hover:text-white">Sou Munícipe</a></li>
                        @endguest
                    </ul>
                </div>

                {{-- Informações --}}
                <div>
                    <h4 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-300">Informações</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            Dados de fonte oficial
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18"/></svg>
                            Receita Federal do Brasil
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            {{ trim((string) ($tenant->portal_nome_cidade ?: $tenant->cidade ?: $tenant->nome_fantasia ?: $tenant->name)) }}
                            @if($tenant->estado)
                                , {{ $tenant->estado }}
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 border-t border-slate-800 pt-8 flex flex-col items-center gap-1 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ $tenant->portalBrandTitle() }} — Todos os direitos reservados.</p>
                <p>Dados sincronizados com a Receita Federal do Brasil · Transparência econômica municipal.</p>
            </div>
        </div>
    </footer>
    @stack('scripts')
    <script>
        // Mobile menu toggle
        (function () {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('icon-open');
            const iconClose = document.getElementById('icon-close');
            if (!btn) return;
            btn.addEventListener('click', function () {
                const isHidden = menu.classList.contains('hidden');
                menu.classList.toggle('hidden', !isHidden);
                iconOpen.classList.toggle('hidden', isHidden);
                iconClose.classList.toggle('hidden', !isHidden);
            });

            // Scroll shadow on header
            const header = document.getElementById('main-header');
            window.addEventListener('scroll', function () {
                header.classList.toggle('shadow-md', window.scrollY > 10);
                header.classList.toggle('border-slate-200/80', window.scrollY > 10);
            }, { passive: true });
        })();
    </script>
</body>
</html>
