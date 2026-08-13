@props([
    'title' => null,
])
@php
    $u = auth()->user();
    $tenant = $u?->tenant;
    $tenant?->loadMissing('adminSetting');
    $logoPath = $tenant?->adminSetting?->logo_prefeitura_path;
    $logoUrl = $logoPath ? asset('storage/'.$logoPath) : null;
    $tenantLabel = $tenant?->display_name ?? $tenant?->name ?? 'Escola';
    $nameParts = array_values(array_filter(preg_split('/\s+/', trim((string) ($u?->name ?? ''))) ?: []));
    if (count($nameParts) >= 2) {
        $initials = strtoupper(mb_substr($nameParts[0], 0, 1).mb_substr($nameParts[1], 0, 1));
    } elseif (count($nameParts) === 1) {
        $initials = strtoupper(mb_substr($nameParts[0], 0, 2));
    } else {
        $initials = '?';
    }
    $nav = [
        ['label' => 'Início', 'route' => route('app.dashboard'), 'match' => ['app.dashboard']],
        ['label' => 'Meus cursos', 'route' => route('app.turmas.index'), 'match' => ['app.turmas.*']],
        ['label' => 'Quizzes', 'route' => route('app.quizzes.index'), 'match' => ['app.quizzes.*']],
        ['label' => 'Certificados', 'route' => route('app.certificados.index'), 'match' => ['app.certificados.*']],
        ['label' => 'Dados cadastrais', 'route' => route('app.cadastro.edit'), 'match' => ['app.cadastro.*']],
        ['label' => 'Senha', 'route' => route('app.senha.edit'), 'match' => ['app.senha.*']],
        ['label' => 'Inscrições', 'route' => route('app.inscricoes.index'), 'match' => ['app.inscricoes.*']],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? null) ? $title.' — ' : '' }}Área do aluno · {{ $tenantLabel }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased selection:bg-cyan-500/30 selection:text-white">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-r border-slate-800/80 bg-slate-900/95 lg:flex lg:flex-col">
            <div class="border-b border-slate-800/80 p-5">
                <a href="{{ route('app.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-sm font-bold text-white shadow-lg shadow-cyan-500/20">EL</span>
                    <span class="leading-tight">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">EAD</span>
                        <span class="block text-sm font-bold text-white">{{ $tenantLabel }}</span>
                    </span>
                </a>
            </div>
            <nav class="flex-1 space-y-0.5 overflow-y-auto p-3" aria-label="Menu do aluno">
                @foreach ($nav as $item)
                    @php $active = collect($item['match'])->contains(fn ($p) => request()->routeIs($p)); @endphp
                    <a href="{{ $item['route'] }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition
                       {{ $active ? 'bg-white/10 text-white ring-1 ring-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-slate-800/80 p-4">
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-slate-700 bg-slate-800/50 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-800">Sair</button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-900/90 backdrop-blur-xl lg:hidden">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <a href="{{ route('app.dashboard') }}" class="text-sm font-bold text-white">Área do aluno</a>
                    <form method="POST" action="{{ route('tenant.logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-cyan-400">Sair</button>
                    </form>
                </div>
                <nav class="flex gap-1 overflow-x-auto border-t border-slate-800/60 px-2 py-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($nav as $item)
                        @php $active = collect($item['match'])->contains(fn ($p) => request()->routeIs($p)); @endphp
                        <a href="{{ $item['route'] }}"
                           class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold whitespace-nowrap {{ $active ? 'bg-cyan-500 text-slate-950' : 'text-slate-400' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </header>

            <header class="sticky top-0 z-30 hidden border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-xl lg:block">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-cyan-400/90">Área do aluno</p>
                        <h1 class="text-lg font-bold text-white">{{ $title ?? 'Painel' }}</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="" class="hidden h-9 w-auto max-w-28 object-contain opacity-90 md:block" />
                        @endif
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-bold text-white ring-1 ring-white/10">{{ $initials }}</span>
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-white">{{ $u?->name }}</p>
                            <p class="max-w-xs truncate text-xs text-slate-400">{{ $u?->email }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                        <p class="font-semibold">Corrija os campos abaixo:</p>
                        <ul class="mt-2 list-inside list-disc text-rose-100/90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-2 rounded-2xl border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-50">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">{{ session('error') }}</div>
                @endif
                @if (session('info'))
                    <div class="mb-6 rounded-2xl border border-cyan-500/35 bg-cyan-500/10 px-4 py-3 text-sm text-cyan-50">{{ session('info') }}</div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
