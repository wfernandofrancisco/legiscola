@props([
    'title' => null,
    'subtitle' => null,
    'hideHeading' => false,
    'empresa' => null,
])
@php
    $u = auth()->user();
    $tenant = $u?->tenant;
    $tenant?->loadMissing('adminSetting');
    $logoPath = $tenant?->adminSetting?->logo_prefeitura_path;
    $logoUrl = $logoPath ? asset('storage/'.$logoPath) : null;
    $tenantLabel = $tenant?->display_name ?? $tenant?->name ?? 'Município';
    $routeEmpresa = $empresa ?? request()->route('empresa');
    $empresaLabel = $routeEmpresa instanceof \App\Models\Empresa
        ? ($routeEmpresa->nome_fantasia ?: $routeEmpresa->razao_social)
        : null;
    $nameParts = array_values(array_filter(preg_split('/\s+/', trim((string) ($u?->name ?? ''))) ?: []));
    if (count($nameParts) >= 2) {
        $initials = strtoupper(mb_substr($nameParts[0], 0, 1).mb_substr($nameParts[1], 0, 1));
    } elseif (count($nameParts) === 1) {
        $initials = strtoupper(mb_substr($nameParts[0], 0, 2));
    } else {
        $initials = '?';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? null) ? $title.' — ' : '' }}DesenvolveCity · {{ $tenantLabel }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased selection:bg-cyan-500/20 selection:text-slate-900">
    {{-- Topbar institucional + navegação (sem sidebar) --}}
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 shadow-sm shadow-slate-900/5 backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-17 flex-wrap items-center justify-between gap-x-4 gap-y-3 py-3">
                {{-- Marca: DesenvolveCity • Tenant --}}
                <a href="{{ route('responsible.dashboard') }}" class="group flex min-w-0 max-w-full flex-1 items-center gap-3 sm:max-w-md lg:max-w-xl">
                    <span class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-linear-to-br from-slate-800 via-slate-900 to-cyan-900 shadow-md ring-1 ring-white/10 transition group-hover:shadow-lg group-hover:ring-cyan-500/30">
                        <img src="{{ asset('img/logo2.png') }}" alt="" class="h-7 w-7 object-contain opacity-95" width="28" height="28" />
                    </span>
                    <span class="min-w-0 text-left">
                        <span class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
                            <span class="text-sm font-bold tracking-tight text-slate-900">DesenvolveCity</span>
                            <span class="hidden text-slate-300 sm:inline" aria-hidden="true">·</span>
                            <span class="block w-full truncate text-sm font-semibold text-cyan-700 sm:inline sm:w-auto sm:max-w-48 md:max-w-xs lg:max-w-md">{{ $tenantLabel }}</span>
                        </span>
                        <span class="mt-0.5 block text-[11px] font-medium uppercase tracking-wider text-slate-500">Painel da empresa</span>
                    </span>
                </a>

                {{-- Prefeitura (opcional) + utilizador --}}
                <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="" class="hidden h-10 w-auto max-w-30 object-contain opacity-90 sm:block md:max-w-35" />
                    @endif
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/80 py-1.5 pl-1.5 pr-2 shadow-inner sm:pr-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-cyan-500 to-blue-600 text-xs font-bold text-white shadow-sm" title="{{ $u?->name }}">{{ $initials }}</span>
                        <div class="hidden min-w-0 max-w-40 text-left sm:block md:max-w-56">
                            <p class="truncate text-xs font-semibold text-slate-900">{{ $u?->name }}</p>
                            @if ($empresaLabel)
                                <p class="truncate text-[10px] font-medium text-slate-500" title="{{ $empresaLabel }}">{{ $empresaLabel }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('tenant.logout') }}" class="shrink-0 border-l border-slate-200/80 pl-2 sm:pl-3">
                            @csrf
                            <button type="submit" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900">Sair</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Menu: só rotas que existem no Registro (Legiscola); evita RouteNotFoundException --}}
            <nav class="flex gap-1 overflow-x-auto border-t border-slate-100/90 py-2 [-ms-overflow-style:none] [scrollbar-width:none] sm:gap-2 [&::-webkit-scrollbar]:hidden" aria-label="Menu principal">
                @php
                    $nav = collect([
                        ['label' => 'Dashboard', 'name' => 'responsible.dashboard', 'match' => ['responsible.dashboard']],
                        ['label' => 'Meu perfil', 'name' => 'responsible.perfil.edit', 'match' => ['responsible.perfil.*']],
                        ['label' => 'Quiz turmas', 'name' => 'responsible.quizzes.index', 'match' => ['responsible.quizzes.*']],
                        ['label' => 'Turmas / presença', 'name' => 'professor.turmas.index', 'match' => ['professor.turmas.*'], 'require_docente_portal' => true],
                        ['label' => 'Painel docente', 'name' => 'professor.dashboard', 'match' => ['professor.*'], 'require_docente' => true],
                    ])
                        ->filter(function ($item) {
                            if (! \Illuminate\Support\Facades\Route::has($item['name'])) {
                                return false;
                            }
                            $user = auth()->user();
                            if (($item['require_docente_portal'] ?? false) && (! $user || ! $user->accessesDocentePortal())) {
                                return false;
                            }
                            if (($item['require_docente'] ?? false) && (! $user || ! $user->isTenantProfessor())) {
                                return false;
                            }

                            return true;
                        })
                        ->map(fn ($item) => array_merge($item, ['href' => route($item['name'])]))
                        ->values()
                        ->all();
                @endphp
                @foreach ($nav as $item)
                    @php
                        $active = collect($item['match'])->contains(fn ($pattern) => request()->routeIs($pattern));
                    @endphp
                    <a href="{{ $item['href'] }}"
                       class="inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full px-3.5 py-2 text-xs font-semibold transition sm:px-4 sm:text-sm
                       {{ $active
                           ? 'bg-linear-to-r from-slate-800 via-slate-900 to-slate-800 text-white shadow-md shadow-slate-900/25 ring-1 ring-white/10'
                           : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        @if ($item['label'] === 'Meu perfil')
                            <svg class="h-4 w-4 shrink-0 opacity-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        @elseif ($item['label'] === 'Painel docente')
                            <svg class="h-4 w-4 shrink-0 opacity-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.886 6.834 50.7 50.7 0 0 0-2.659.814m0 9.957a54.774 54.774 0 0 1-5.834 6.957 59.894 59.894 0 0 1-10.892-7.084 50.7 50.7 0 0 1 5.826-9.087"/></svg>
                        @else
                            <svg class="h-4 w-4 shrink-0 opacity-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    @unless ($hideHeading)
        <div class="border-b border-slate-200/60 bg-linear-to-r from-slate-900 via-slate-800 to-cyan-900 text-white">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                @if ($title)
                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1>
                @endif
                @isset($subtitle)
                    <p class="{{ ($title ?? null) ? 'mt-2' : '' }} max-w-3xl text-sm leading-relaxed text-cyan-100/90 sm:text-base">{{ $subtitle }}</p>
                @endisset
            </div>
        </div>
    @endunless

    @if (session('success') || session('error') || session('info'))
        <div class="fixed top-20 right-4 z-50 flex flex-col gap-3 sm:right-6"
             x-data="{ toasts: [] }"
             x-init="
                @if(session('success')) toasts.push({ id: 1, type: 'success', msg: {{ json_encode(session('success')) }} }); @endif
                @if(session('error'))  toasts.push({ id: 2, type: 'error',   msg: {{ json_encode(session('error')) }} });   @endif
                @if(session('info'))   toasts.push({ id: 3, type: 'info',    msg: {{ json_encode(session('info')) }} });    @endif
                toasts.forEach(t => setTimeout(() => toasts = toasts.filter(x => x.id !== t.id), 5000))
             ">
            <template x-for="t in toasts" :key="t.id">
                <div x-show="true"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-8"
                     :class="{
                         'border-emerald-200 bg-emerald-50 text-emerald-950': t.type === 'success',
                         'border-red-200 bg-red-50 text-red-950':             t.type === 'error',
                         'border-amber-200 bg-amber-50 text-amber-950':       t.type === 'info',
                     }"
                     class="flex w-80 max-w-[calc(100vw-2rem)] items-start gap-3 rounded-2xl border px-4 py-3.5 text-sm font-medium shadow-xl backdrop-blur">
                    <span x-show="t.type === 'success'" class="mt-0.5 text-emerald-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    </span>
                    <span x-show="t.type === 'error'" class="mt-0.5 text-red-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                    </span>
                    <span x-show="t.type === 'info'" class="mt-0.5 text-amber-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                    </span>
                    <span class="flex-1" x-text="t.msg"></span>
                    <button @click="toasts = toasts.filter(x => x.id !== t.id)" class="ml-auto shrink-0 opacity-50 hover:opacity-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>
    @endif

    <main class="w-full flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200/80 bg-white/80 py-6 text-center text-[11px] font-medium text-slate-500 backdrop-blur">
        <p>DesenvolveCity — plataforma de inteligência econômica municipal</p>
        <p class="mt-1 text-slate-400">{{ $tenantLabel }}</p>
    </footer>

    @stack('scripts')
</body>
</html>
