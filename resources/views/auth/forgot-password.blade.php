@extends('layouts.guest')

@php
    $brandName = filled($settings?->nome_camara)
        ? $settings->nome_camara
        : (isset($tenant) ? ($tenant->display_name ?? $tenant->name) : config('app.name'));
    $logoSrc = file_exists(public_path('img/logo.png'))
        ? asset('img/logo.png')
        : asset('img/legiscola.svg');
@endphp

@section('title', 'Recuperar senha — ' . $brandName)

@section('content')
<div class="relative min-h-full overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-sky-200 via-emerald-50 to-amber-100"></div>
    <div class="auth-blob-a pointer-events-none absolute -left-24 -top-28 h-[28rem] w-[28rem] rounded-full bg-sky-400/40 blur-3xl"></div>
    <div class="auth-blob-b pointer-events-none absolute -right-20 top-10 h-[24rem] w-[24rem] rounded-full bg-emerald-400/35 blur-3xl"></div>
    <div class="auth-sun pointer-events-none absolute right-[18%] top-8 h-40 w-40 rounded-full bg-amber-300/70 blur-2xl"></div>

    <header class="relative z-20 mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="h-9 w-auto object-contain">
        </a>
        <a href="{{ route('tenant.login') }}" class="rounded-full bg-white/70 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-sm ring-1 ring-white/80 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white">
            ← Entrar
        </a>
    </header>

    <div class="relative z-10 mx-auto grid min-h-[calc(100vh-5.5rem)] max-w-6xl items-center gap-10 px-5 pb-12 pt-4 sm:px-8 lg:grid-cols-12">
        <div class="lg:col-span-6">
            <p class="auth-in auth-d1 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-800">Recuperação</p>
            <h1 class="auth-in auth-d2 font-display mt-4 max-w-lg text-5xl font-extrabold leading-[0.95] tracking-tight text-slate-900 sm:text-6xl">
                A gente
                <span class="font-serif block italic font-medium text-sky-700">te devolve o acesso.</span>
            </h1>
            <p class="auth-in auth-d3 mt-5 max-w-md text-lg text-slate-600">
                Link no e-mail, válido por uma hora. A senha atual continua valendo até você trocar.
            </p>
        </div>

        <div class="auth-in auth-d4 lg:col-span-6 lg:justify-self-end lg:w-full lg:max-w-md">
            <div class="rounded-[1.75rem] bg-white/80 p-6 shadow-2xl shadow-sky-400/25 ring-1 ring-white backdrop-blur-xl sm:p-8">
                <h2 class="font-display text-2xl font-extrabold text-slate-900">Esqueceu a senha?</h2>
                <p class="mt-1 text-sm text-slate-500">Informe o e-mail da gestão.</p>

                @if (session('status'))
                    <div class="mt-5 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">E-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="auth-field w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm outline-none {{ $errors->has('email') ? 'border-red-400' : '' }}"
                               placeholder="gestao@camara.gov.br" />
                    </div>
                    <x-turnstile />
                    <button type="submit" class="auth-btn flex w-full cursor-pointer items-center justify-center rounded-2xl px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-500/30">
                        Enviar link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
