<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name') }}</title>
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
                                {{ auth()->user()->tenant->name ?? 'Admin' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto dark:bg-gray-800">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.relatorios.sistema') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.relatorios.*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-6a2 2 0 012-2h8M9 17H7a2 2 0 01-2-2V7a2 2 0 012-2h8l4 4v8a2 2 0 01-2 2h-2m-8 0h8m-8 0a2 2 0 002 2h2a2 2 0 002-2m-8 0V9a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H19" />
                    </svg>
                    Relatórios
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.users*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Usuários
                </a>

                <a href="{{ route('admin.cursos.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.cursos*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 4.462 5 2 6.462 2 8.25v9C2 15.462 4.462 14 7.5 14c1.746 0 3.332.477 4.5 1.253m0-9C13.168 5.477 14.754 5 16.5 5c3.038 0 5.5 1.462 5.5 3.25v9c0-1.788-2.462-3.25-5.5-3.25-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Cursos
                </a>

                <a href="{{ route('admin.professores.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.professores*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7zm7-1a3 3 0 100-6 3 3 0 000 6zM2 20a6 6 0 016-6" />
                    </svg>
                    Professores
                </a>

                <a href="{{ route('admin.turmas.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.turmas*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M8 11h8M8 15h5" />
                    </svg>
                    Turmas
                </a>

                <a href="{{ route('admin.aulas.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.aulas*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zM3 9v6l9 5 9-5V9" />
                    </svg>
                    Aulas
                </a>

                <a href="{{ route('admin.eventos.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.eventos*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v11a2 2 0 002 2z" />
                    </svg>
                    Eventos
                </a>

                <a href="{{ route('admin.quizzes.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.quizzes.*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>
                    Quizzes
                </a>

                <a href="{{ route('admin.alunos.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.alunos.index') || request()->routeIs('admin.alunos.create') || request()->routeIs('admin.alunos.edit') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 19a6 6 0 10-12 0m12 0a6 6 0 0112 0M12 11a4 4 0 100-8 4 4 0 000 8zm12 2a4 4 0 10-8 0 4 4 0 008 0z" />
                    </svg>
                    Alunos
                </a>

                <a href="{{ route('admin.alunos.mapa') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.alunos.mapa*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Mapa de alunos
                </a>

                <a href="{{ route('admin.professores-credenciamentos.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.professores-credenciamentos*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zM7 8h10M7 12h8M7 16h6" />
                    </svg>
                    Credenciamento Docente
                </a>

                <a href="{{ route('admin.sobre-escola.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.sobre-escola*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 1.343-3 3v7h6v-7c0-1.657-1.343-3-3-3zM4 10h16M5 10V8a7 7 0 0114 0v2" />
                    </svg>
                    Sobre a Escola
                </a>

                <a href="{{ route('admin.templates-certificado.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.templates-certificado*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>
                    Templates Certificado
                </a>

                <a href="{{ route('admin.noticias.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.noticias*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zM7 8h10M7 12h6" />
                    </svg>
                    Notícias
                </a>

                <a href="{{ route('admin.contatos-portal.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.contatos-portal*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Contatos do portal
                </a>













                <a href="{{ route('admin.settings.edit') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.settings*') ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317a1 1 0 011.35-.936l1.646.732a1 1 0 001.056-.21l1.282-1.282a1 1 0 011.414 0l1.414 1.414a1 1 0 010 1.414l-1.282 1.282a1 1 0 00-.21 1.056l.732 1.646a1 1 0 01-.936 1.35h-1.814a1 1 0 00-.95.684l-.572 1.716a1 1 0 01-.949.684h-2a1 1 0 01-.949-.684l-.572-1.716a1 1 0 00-.95-.684H5.63a1 1 0 01-.936-1.35l.732-1.646a1 1 0 00-.21-1.056L3.934 5.53a1 1 0 010-1.414L5.348 2.7a1 1 0 011.414 0l1.282 1.282a1 1 0 001.056.21l1.225-.875z" />
                    </svg>
                    Configurações
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
                        <p class="text-xs text-gray-400">Admin</p>
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

                    <a href="{{ route('admin.profile.edit') }}" title="Editar perfil"
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

    @if (isset($scripts))
        {!! $scripts !!}
    @endif

    @stack('scripts')
</body>

</html>
