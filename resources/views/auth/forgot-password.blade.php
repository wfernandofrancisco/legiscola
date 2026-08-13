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
<div class="flex min-h-full">

    <aside class="auth-ink relative hidden w-[46%] overflow-hidden text-[#f4efe6] lg:flex xl:w-[44%]">
        <div class="auth-grid pointer-events-none absolute inset-0"></div>
        <div class="auth-grain pointer-events-none absolute inset-0"></div>
        <div class="pointer-events-none absolute -left-24 top-24 h-80 w-80 rounded-full bg-amber-700/10 blur-3xl"></div>
        <div class="absolute inset-y-0 right-0 w-px bg-linear-to-b from-transparent via-amber-200/25 to-transparent"></div>

        <div class="relative z-10 flex w-full flex-col justify-between px-12 py-11 xl:px-16">
            <div class="auth-animate auth-d1 flex items-start justify-between gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if(!empty($settings?->logo_prefeitura_path))
                        <span class="flex items-center gap-3 rounded-2xl bg-white/5 p-2.5 ring-1 ring-white/10">
                            <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="h-10 w-auto object-contain">
                            <img src="{{ asset('storage/'.$settings->logo_prefeitura_path) }}" alt="{{ $brandName }}" class="h-10 w-auto object-contain">
                        </span>
                    @elseif(isset($tenant))
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/8 font-display text-lg font-semibold ring-1 ring-white/15">
                            {{ $tenant->portalBrandInitials() }}
                        </span>
                    @else
                        <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="h-11 w-auto object-contain">
                    @endif
                </a>
                <a href="{{ route('tenant.login') }}" class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-100/70 transition hover:text-amber-50">
                    Entrar
                </a>
            </div>

            <div class="max-w-md">
                <p class="auth-animate auth-d2 mb-5 inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-200/80">
                    <span class="h-px w-8 bg-amber-200/70" aria-hidden="true"></span>
                    Segurança
                </p>
                <h1 class="auth-animate auth-d3 font-display text-[2.35rem] font-semibold leading-[1.12] tracking-tight text-white xl:text-[2.7rem]">
                    Recupere o acesso<br>
                    <em class="font-medium not-italic text-amber-100/90">com calma e segurança.</em>
                </h1>
                <p class="auth-animate auth-d4 mt-5 max-w-sm text-[15px] leading-relaxed text-slate-300/85">
                    Enviamos um link temporário para o e-mail cadastrado. A senha atual continua válida até você alterá-la.
                </p>

                <ul class="auth-animate auth-d5 mt-10 space-y-3.5 border-t border-white/10 pt-8 text-sm text-slate-200/85">
                    <li>Link válido por 60 minutos</li>
                    <li>Verifique também spam e lixo eletrônico</li>
                    <li>Use o mesmo e-mail da gestão da câmara</li>
                </ul>
            </div>

            <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400/80">
                {{ config('app.name') }} · recuperação de senha
            </p>
        </div>
    </aside>

    <section class="relative flex flex-1 flex-col justify-center px-6 py-12 sm:px-10 lg:px-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgb(196_165_116/0.09),_transparent_55%)]"></div>

        <div class="relative mx-auto w-full max-w-[26rem]">
            <div class="mb-10 flex items-center justify-between lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="h-9 w-auto object-contain">
                </a>
                <a href="{{ route('tenant.login') }}" class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500 hover:text-[#001823]">Login</a>
            </div>

            <p class="auth-animate auth-d1 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-800/70">Recuperação de acesso</p>
            <h2 class="auth-animate auth-d2 font-display mt-2 text-3xl font-semibold tracking-tight text-[#001823]">Esqueceu a senha?</h2>
            <p class="auth-animate auth-d3 mt-2 text-sm leading-relaxed text-stone-600">
                Informe o e-mail cadastrado. Enviaremos um link seguro para redefinir.
            </p>

            @if (session('status'))
                <div class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200/80 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-900">
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200/80 bg-red-50/90 px-4 py-3.5 text-sm text-red-900">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="auth-animate auth-d4 mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-600">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="auth-input w-full rounded-xl border border-stone-300/80 bg-white/80 px-4 py-3.5 text-sm text-[#001823] outline-none transition placeholder:text-stone-400 {{ $errors->has('email') ? 'border-red-400' : '' }}"
                           placeholder="gestao@camara.gov.br" />
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <x-turnstile />
                @error('cf-turnstile-response')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="group flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-[#001823] px-6 py-3.5 text-sm font-semibold text-[#f4efe6] shadow-[0_12px_30px_-12px_rgba(0,24,35,0.55)] transition hover:bg-[#01293a] active:scale-[0.99]">
                    Enviar link de recuperação
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>

            <p class="mt-10 border-t border-stone-300/60 pt-6 text-center text-sm text-stone-500">
                Lembrou a senha?
                <a href="{{ route('tenant.login') }}" class="font-semibold text-[#001823] underline decoration-stone-300 underline-offset-4 hover:decoration-[#001823]">Entrar na conta</a>
            </p>
        </div>
    </section>
</div>
@endsection
