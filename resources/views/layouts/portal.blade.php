<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak]{display:none !important}</style>
    @php
        $portalPageTitle = trim($__env->yieldContent('title'));
        if ($portalPageTitle === '') {
            $portalPageTitle = ($portalTenant?->display_name ?? config('app.name')).' — Escola Legislativa';
        } else {
            $portalPageTitle .= ' · '.($portalTenant?->display_name ?? config('app.name'));
        }
    @endphp
    <title>{{ $portalPageTitle }}</title>
    <meta name="description" content="@yield('meta_description', (($portalTenant?->nome_fantasia ?? $portalTenant?->name) ?: config('app.name')).' — Escola Legislativa')">
    <meta property="og:title" content="@yield('og_title', $portalTenant?->display_name ?? config('app.name'))">
    <meta property="og:description" content="@yield('og_description', 'Portal institucional da Escola Legislativa')">
    <meta property="og:type" content="website">
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Animate.css vem do Vite (resources/css/app.css → @import 'animate.css'). Flowbite removido: não era usado nas views. --}}
    <style id="portal-theme-vars">
        #portal-shell { {{ $portalThemeCss ?? '' }} }
    </style>
    <style id="portal-header-skin">
        /* Topo da página — vidro leve */
        #portal-site-header.portal-header-surface:not(.portal-header--scrolled) {
            background: rgb(255 255 255 / 0.94);
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgb(226 232 240 / 0.85);
            box-shadow: inset 0 -1px 0 rgb(15 23 42 / 0.04);
        }
        html.dark #portal-site-header.portal-header-surface:not(.portal-header--scrolled) {
            background: rgb(15 23 42 / 0.94);
            border-bottom-color: rgb(51 65 85 / 0.65);
        }

        /* Rolagem — NÃO depende de Alpine: classe .portal-header--scrolled é toggled por JS inline */
        #portal-site-header.portal-header--scrolled {
            -webkit-backdrop-filter: none;
            backdrop-filter: none;
            background-color: rgb(15 23 42);
            background-image: linear-gradient(
                180deg,
                color-mix(in srgb, var(--portal-secondary, #1e3a8a), rgb(15 23 42) 55%) 0%,
                rgb(15 23 42) 45%,
                rgb(8 12 28) 100%
            );
            border-bottom: 1px solid rgb(255 255 255 / 0.12);
            box-shadow: 0 10px 40px -12px rgb(15 23 42 / 0.55);
        }
        #portal-site-header.portal-header--scrolled::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--portal-primary, #3b82f6), var(--portal-tertiary, #22d3ee), var(--portal-primary, #3b82f6));
            pointer-events: none;
        }

        /* Texto / links quando rolou */
        #portal-site-header.portal-header--scrolled .portal-header-eyebrow {
            color: rgb(224 242 254 / 0.92) !important;
        }
        #portal-site-header.portal-header--scrolled .portal-header-title {
            color: #fff !important;
            text-shadow: 0 1px 8px rgb(0 0 0 / 0.25);
        }
        #portal-site-header.portal-header--scrolled .portal-header-divider {
            background-color: rgb(255 255 255 / 0.35) !important;
        }
        #portal-site-header.portal-header--scrolled .portal-logo-platform {
            filter: brightness(0) invert(1);
            opacity: 0.95;
        }
        /* Logo Legiscola (cores): manter sem invert no topo escuro */
        #portal-site-header.portal-header--scrolled .portal-header-legiscola-logo img {
            filter: drop-shadow(0 1px 2px rgb(0 0 0 / 0.45));
        }
        #portal-site-header.portal-header--scrolled .portal-nav-desk-link {
            color: #fff !important;
        }
        #portal-site-header.portal-header--scrolled .portal-nav-desk-link:hover {
            background-color: rgb(255 255 255 / 0.12);
        }
        #portal-site-header.portal-header--scrolled .portal-nav-desk-link.is-active {
            background-color: #fff !important;
            color: rgb(15 23 42) !important;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.08);
        }
        #portal-site-header.portal-header--scrolled .portal-header-cta {
            border: 2px solid rgb(255 255 255 / 0.65);
            background: transparent !important;
            background-image: none !important;
            color: #fff !important;
        }
        #portal-site-header.portal-header--scrolled .portal-header-cta:hover {
            background-color: rgb(255 255 255 / 0.1) !important;
        }
        #portal-site-header.portal-header--scrolled .portal-header-menu-btn {
            color: #fff !important;
        }
        #portal-site-header.portal-header--scrolled .portal-header-menu-btn:hover {
            background-color: rgb(255 255 255 / 0.12);
        }
        #portal-site-header.portal-header--scrolled .portal-mobile-panel {
            border-color: rgb(255 255 255 / 0.12) !important;
            background-color: rgb(0 0 0 / 0.45) !important;
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
        }
        #portal-site-header.portal-header--scrolled .portal-mobile-panel .portal-nav-mobile-a {
            color: rgb(255 255 255 / 0.95) !important;
        }
        #portal-site-header.portal-header--scrolled .portal-mobile-panel .portal-nav-mobile-a:hover {
            background-color: rgb(255 255 255 / 0.12) !important;
        }
        #portal-site-header.portal-header--scrolled .portal-mobile-panel hr {
            border-color: rgb(255 255 255 / 0.22) !important;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-50">
<div id="portal-shell" class="flex min-h-screen flex-col">

    {{-- Header: scroll via JS inline; menu mobile via toggle vanilla (não depende de Alpine/Vite). --}}
    <header
        id="portal-site-header"
        class="portal-header-surface relative sticky top-0 z-50 transition-[background-color,box-shadow,border-color] duration-300 ease-out"
    >
        @php($pf = $portalPlatform ?? config('portal.platform', []))
        @php($pfLogo = $pf['logo_path'] ?? null)
        <nav class="mx-auto flex max-w-7xl items-start justify-between gap-4 px-4 py-3 sm:items-center sm:py-4 sm:gap-6 sm:px-6 lg:px-8">
            @php($legiscolaLogoPath = 'img/logo.png')
            {{-- Linha 1: logos · Linha 2: "Escola Legislativa" + nome do tenant (abaixo dos dois) --}}
            <div class="flex min-w-0 flex-1 flex-col gap-1 sm:gap-1.5">
                <div class="flex min-w-0 items-center gap-3 sm:gap-5">
                    @if(file_exists(public_path($legiscolaLogoPath)))
                        <a href="{{ route('home') }}" class="portal-header-legiscola-logo flex shrink-0 items-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--portal-primary,#3b82f6)] focus-visible:ring-offset-2 rounded-md"
                           aria-label="{{ config('app.name') }} — início">
                            <img src="{{ asset($legiscolaLogoPath) }}" alt="{{ config('app.name') }}" width="120" height="48" fetchpriority="high"
                                 class="h-8 max-w-[min(140px,34vw)] w-auto object-contain object-left sm:h-9 sm:max-w-[min(180px,30vw)]"/>
                        </a>
                        <span class="portal-header-divider hidden min-h-[2.75rem] w-px shrink-0 self-stretch bg-slate-200 sm:block dark:bg-slate-600" aria-hidden="true"></span>
                    @endif
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--portal-primary,#3b82f6)] focus-visible:ring-offset-2 rounded-md"
                       aria-label="{{ ($portalTenant?->portalBrandTitle()) ?? __('Início') }}">
                        @if(!empty($portalAdminSettings?->logo_prefeitura_path))
                            <img src="{{ asset('storage/'.$portalAdminSettings->logo_prefeitura_path) }}"
                                 alt=""
                                 loading="lazy"
                                 class="h-11 w-auto max-h-11 shrink-0 object-contain"/>
                        @else
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-md"
                                  style="background:linear-gradient(135deg, var(--portal-primary,#3b82f6),var(--portal-secondary,#1e40af))">
                                {{ $portalTenant?->portalBrandInitials() ?? 'EL' }}
                            </span>
                        @endif
                    </a>
                </div>
                <a href="{{ route('home') }}" class="min-w-0 max-w-full self-start overflow-hidden text-left leading-tight focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--portal-primary,#3b82f6)] focus-visible:ring-offset-2 rounded-md -mt-px sm:-mt-0">
                    <span class="portal-header-eyebrow block text-xs font-semibold uppercase tracking-wide text-slate-500 transition-colors duration-300 sm:text-sm dark:text-slate-400">
                        Escola Legislativa
                    </span>
                    <span class="portal-header-title line-clamp-2 text-pretty block text-base font-bold leading-snug text-slate-900 transition-colors duration-300 sm:text-lg dark:text-white">
                        @if($pfLogo && is_string($pfLogo) && file_exists(public_path($pfLogo)))
                            {{ $portalAdminSettings?->nome_camara ?: $portalTenant?->portalChamberBrandLine() }}
                        @else
                            {{ $portalTenant?->portalBrandTitle() }}
                        @endif
                    </span>
                </a>
            </div>

            <div class="hidden items-center gap-1 lg:flex">
                @include('portal.partials.nav-desktop')
            </div>

            <div class="flex items-center gap-2">
                
                <a href="{{ route('portal.acesso.docente.login') }}"
                   class="hidden rounded-full border-2 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 sm:inline-flex dark:border-slate-500 dark:text-slate-100 dark:hover:bg-slate-800"
                   style="border-color:color-mix(in srgb,var(--portal-primary,#3b82f6),transparent 65%)">
                    Docente
                </a>
                <a href="{{ route('portal.acesso.login') }}"
                   class="portal-header-cta hidden rounded-full border border-transparent px-5 py-2 text-sm font-semibold text-white shadow-md transition hover:opacity-95 sm:inline-flex"
                   style="background-image:linear-gradient(135deg,var(--portal-primary,#3b82f6),var(--portal-secondary,#1e40af))">
                    Área do aluno
                </a>
                <button type="button"
                        id="portal-menu-toggle"
                        class="portal-header-menu-btn inline-flex rounded-lg p-2 text-slate-700 transition hover:bg-white/75 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400/50 lg:hidden dark:text-slate-100 dark:hover:bg-slate-800"
                        aria-expanded="false"
                        aria-controls="portal-menu-panel">
                    <span class="sr-only">Abrir ou fechar menu</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </nav>

        <div id="portal-menu-panel"
             class="portal-mobile-panel hidden border-t border-slate-100 bg-white px-4 py-4 lg:hidden dark:border-slate-800 dark:bg-slate-950"
             role="navigation"
             aria-label="Menu principal">
            @include('portal.partials.nav-mobile')
        </div>
    </header>
    <script>
        (function () {
            function bindPortalMobileMenu() {
                var btn = document.getElementById('portal-menu-toggle');
                var panel = document.getElementById('portal-menu-panel');
                if (!btn || !panel) {
                    return;
                }
                btn.addEventListener('click', function () {
                    if (panel.classList.contains('hidden')) {
                        panel.classList.remove('hidden');
                        btn.setAttribute('aria-expanded', 'true');
                    } else {
                        panel.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                    }
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindPortalMobileMenu);
            } else {
                bindPortalMobileMenu();
            }
        })();
    </script>
    <script>
        (function () {
            var el = document.getElementById('portal-site-header');
            if (!el) return;
            var threshold = 24;
            function y() {
                return window.pageYOffset || document.documentElement.scrollTop || 0;
            }
            function sync() {
                el.classList.toggle('portal-header--scrolled', y() > threshold);
            }
            function bind() {
                sync();
                window.addEventListener('scroll', sync, { passive: true });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bind);
            } else {
                bind();
            }
        })();
    </script>

    @if(session('success'))
        <div class="border-b border-emerald-100 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-900 dark:bg-emerald-950/80 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="border-b border-amber-100 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900 dark:bg-amber-950/80 dark:text-amber-200">
            {{ session('warning') }}
        </div>
    @endif

    <main id="portal-main" class="flex-1 min-h-0">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-slate-900 text-slate-300 dark:border-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:grid lg:gap-12 {{ ! empty($portalMapEmbedUrl ?? null) && ! empty($portalMapOpenUrl ?? null) ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest" style="color:var(--portal-tertiary,#34d399)">{{ $portalTenant?->nome_fantasia ?? $portalTenant?->display_name }}</p>
                <p class="mt-3 text-lg font-semibold text-white">{{ $portalTenant?->portalBrandTitle() }}</p>
                <p class="mt-4 max-w-sm text-sm text-slate-400">
                    Educação cidadã, transparência e excelência tecnológica a serviço da comunidade legislativa.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @if(!empty($portalAdminSettings?->instagram))
                        <a href="{{ $portalAdminSettings->instagram }}" target="_blank" rel="noopener noreferrer"
                           class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 text-slate-400 transition hover:border-slate-500 hover:text-white hover:bg-white/6"
                           aria-label="Instagram">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/></svg>
                        </a>
                    @endif
                    @if(!empty($portalAdminSettings?->x))
                        <a href="{{ $portalAdminSettings->x }}" target="_blank" rel="noopener noreferrer"
                           class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 text-slate-400 transition hover:border-slate-500 hover:text-white hover:bg-white/6"
                           aria-label="X (Twitter)">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z"/></svg>
                        </a>
                    @endif
                    @if(!empty($portalAdminSettings?->facebook))
                        <a href="{{ $portalAdminSettings->facebook }}" target="_blank" rel="noopener noreferrer"
                           class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 text-slate-400 transition hover:border-slate-500 hover:text-white hover:bg-white/6"
                           aria-label="Facebook">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/></svg>
                        </a>
                    @endif
                </div>
            </div>
            <div class="mt-10 grid gap-10 sm:grid-cols-2 lg:col-span-2 lg:mt-0">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Links</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('portal.noticias.index') }}" class="hover:text-white">Notícias</a></li>
                        <li><a href="{{ route('portal.eventos.index') }}" class="hover:text-white">Eventos</a></li>
                        <li><a href="{{ route('portal.cursos.index') }}" class="hover:text-white">Turmas</a></li>
                        <li><a href="{{ route('portal.professores.index') }}" class="hover:text-white">Professores</a></li>
                        <li><a href="{{ route('portal.sobre') }}" class="hover:text-white">Institucional</a></li>
                        <li><a href="{{ route('portal.tutorial') }}" class="hover:text-white">Tutorial do portal</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Contato</h3>
                    <address class="mt-4 space-y-1 text-sm not-italic text-slate-400">
                        @if(!empty($portalAdminSettings?->logradouro))
                            <p>{{ trim($portalAdminSettings->logradouro.(($portalAdminSettings->numero ?? '') !== '' ? ', '.$portalAdminSettings->numero : '')) }}</p>
                            @if(!empty($portalAdminSettings->bairro) || !empty($portalAdminSettings->cidade))
                                <p>{{ collect([$portalAdminSettings->bairro, $portalAdminSettings->cidade, $portalAdminSettings->uf])->filter()->join(', ') }}</p>
                            @endif
                            @if(!empty($portalAdminSettings->cep))
                                <p>CEP {{ $portalAdminSettings->cep }}</p>
                            @endif
                        @elseif($portalTenant && ($portalTenant->logradouro ?? null))
                            <p>{{ $portalTenant->logradouro }}, {{ $portalTenant->numero }}</p>
                        @endif
                        @if(!empty($portalAdminSettings?->telefone))
                            <p class="mt-2"><span class="text-white">Tel.</span> {{ $portalAdminSettings->telefone }}</p>
                        @endif
                        @if(!empty($portalAdminSettings?->whatsapp))
                            <p><span class="text-white">WhatsApp</span> {{ $portalAdminSettings->whatsapp }}</p>
                        @endif
                        @if(!empty($portalAdminSettings?->email))
                            <p><a href="mailto:{{ $portalAdminSettings->email }}" class="hover:text-white">{{ $portalAdminSettings->email }}</a></p>
                        @endif
                    </address>
                    <div class="mt-4">
                        <a href="{{ route('portal.contato') }}" class="text-sm font-semibold" style="color:var(--portal-primary,#60a5fa)">Formulário de contato →</a>
                    </div>
                </div>
            </div>
            @if(!empty($portalMapEmbedUrl ?? null) && !empty($portalMapOpenUrl ?? null))
                <div class="mt-10 lg:mt-0">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Localização</h3>
                    <a href="{{ $portalMapOpenUrl }}"
                       target="_blank" rel="noopener"
                       class="mt-4 block w-full max-w-[280px] overflow-hidden rounded-xl border border-slate-700 shadow-lg ring-1 ring-black/30 transition hover:ring-2 hover:ring-[color:var(--portal-primary,#60a5fa)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--portal-primary,#60a5fa)]">
                        <span class="sr-only">Abrir mapa maior no Google Maps</span>
                        <iframe title="Miniatura da localização no mapa"
                                loading="lazy"
                                class="pointer-events-none h-[120px] w-full max-w-[280px] border-0 lg:mx-0"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="{{ $portalMapEmbedUrl }}"></iframe>
                    </a>
                    <p class="mt-2 max-w-[280px] text-xs text-slate-500">Toque ou clique para abrir no Google Maps.</p>
                </div>
            @endif
        </div>
        @php($legiscolaFooterLogo = 'img/logo.png')
        <div class="border-t border-slate-800 bg-slate-950 py-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
                    @if(file_exists(public_path($legiscolaFooterLogo)))
                        <a href="{{ route('home') }}" class="inline-flex shrink-0 focus-visible:outline-none focus-visible:ring-2 rounded-md" style="--tw-ring-color:var(--portal-primary,#60a5fd)" aria-label="{{ config('app.name') }}">
                            <img src="{{ asset($legiscolaFooterLogo) }}" alt="{{ config('app.name') }}" width="220" height="52"
                                 class="h-11 w-auto max-w-[240px] object-contain brightness-110 contrast-105 opacity-95"/>
                        </a>
                    @elseif($pfLogo && is_string($pfLogo) && file_exists(public_path($pfLogo)))
                        <img src="{{ asset($pfLogo) }}" alt="" width="100" height="28" class="h-7 w-auto brightness-0 invert opacity-90"/>
                    @endif
                    <div class="text-xs leading-relaxed text-slate-500">
                        <p class="font-semibold text-slate-300">{{ $pf['owner_label'] ?? config('app.name') }}</p>
                        <p class="mt-1">Solução em gestão acadêmica para escolas legislativas.</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 text-sm text-slate-400 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-8">
                    @if(! empty($pf['owner_email'] ?? null))
                        <a href="mailto:{{ $pf['owner_email'] }}" class="font-medium transition hover:text-white" style="color:var(--portal-primary,#93c5fd)">{{ $pf['owner_email'] }}</a>
                    @endif
                    @if(! empty($pf['owner_phone'] ?? null))
                        <a href="tel:{{ preg_replace('/\s+/', '', (string) $pf['owner_phone']) }}" class="hover:text-white">{{ $pf['owner_phone'] }}</a>
                    @endif
                    @if(config('app.debug') && empty($pf['owner_email'] ?? null) && empty($pf['owner_phone'] ?? null))
                        <span class="text-xs text-slate-600">Opcional: defina <code class="rounded bg-slate-800 px-1 text-slate-400">PLATFORM_OWNER_EMAIL</code> no .env.</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="border-t border-slate-900 py-5 text-center text-xs text-slate-600">
            © {{ date('Y') }} {{ $portalTenant?->nome_fantasia ?? $portalTenant?->display_name ?? '' }} ·
            <a href="{{ route('portal.privacidade') }}" class="font-medium underline underline-offset-2 hover:text-slate-400" style="color:var(--portal-primary,#93c5fd)">Privacidade</a>
            · Portal institucional · {{ config('app.name') }}
        </div>
    </footer>
</div>
<x-portal.cookie-consent />
@stack('scripts')

{{-- Opcional: threshold = fração da área do bloco visível (0.1 = 10%). rootMargin em px (ex.: "0px 0px -8% 0px"). --}}
<script>
    window.__portalAnimate = window.__portalAnimate || { revealThreshold: 0.1 };
</script>
</body>
</html>
