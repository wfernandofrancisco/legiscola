@extends('layouts.guest')

@php
    $logoSrc = file_exists(public_path('img/logo.png'))
        ? asset('img/logo.png')
        : asset('img/legiscola.svg');
@endphp

@section('title', 'Diretor Regional — ' . config('app.name'))

@section('content')
<div class="relative min-h-full overflow-hidden">
    {{-- Mesmo céu do login da câmara — identidade compartilhada --}}
    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-sky-200 via-emerald-50 to-amber-100"></div>
    <div class="auth-blob-a pointer-events-none absolute -left-24 -top-28 h-[28rem] w-[28rem] rounded-full bg-sky-400/40 blur-3xl"></div>
    <div class="auth-blob-b pointer-events-none absolute -right-20 top-10 h-[24rem] w-[24rem] rounded-full bg-indigo-400/30 blur-3xl"></div>
    <div class="auth-sun pointer-events-none absolute right-[18%] top-8 h-40 w-40 rounded-full bg-amber-300/70 blur-2xl"></div>
    <div class="auth-blob-a pointer-events-none absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-teal-300/30 blur-3xl" style="animation-delay:-6s"></div>

    <header class="relative z-20 mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="h-9 w-auto object-contain drop-shadow-sm">
        </a>
        <a href="{{ route('home') }}" class="rounded-full bg-white/70 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-sm ring-1 ring-white/80 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white">
            ← Portal
        </a>
    </header>

    <div class="relative z-10 mx-auto grid min-h-[calc(100vh-5.5rem)] max-w-6xl items-center gap-10 px-5 pb-12 pt-4 sm:px-8 lg:grid-cols-12 lg:gap-8">
        {{-- Ênfase no papel de diretor --}}
        <div class="lg:col-span-7">
            <p class="auth-in auth-d1 inline-flex items-center gap-2 rounded-full bg-indigo-600 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white shadow-lg shadow-indigo-500/35">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                </span>
                Diretor Regional
            </p>

            <h1 class="auth-in auth-d2 font-display mt-5 max-w-xl text-5xl font-extrabold leading-[0.95] tracking-tight text-slate-900 sm:text-6xl lg:text-[4.4rem]">
                Sua região,
                <span class="font-serif block italic font-medium text-indigo-700">numa visão só.</span>
            </h1>

            <p class="auth-in auth-d3 mt-5 max-w-md text-lg leading-relaxed text-slate-600">
                Painel da direção regional: câmaras da sua UF, catálogo de conteúdos, licenças e recebíveis —
                sem misturar com o login da gestão local.
            </p>

            <div class="auth-in auth-d4 relative mt-10 hidden h-56 sm:block lg:h-64">
                <div class="auth-float absolute left-0 top-2 w-48 rounded-2xl bg-white/85 p-4 shadow-xl shadow-indigo-300/40 ring-1 ring-white backdrop-blur">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Abrangência</p>
                    <p class="font-display mt-1 text-lg font-bold text-slate-900">Câmaras por UF</p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-indigo-100">
                        <div class="h-full w-4/5 rounded-full bg-indigo-500"></div>
                    </div>
                </div>
                <div class="auth-float-slow absolute left-40 top-16 w-52 rounded-2xl bg-indigo-700 p-4 text-white shadow-xl shadow-indigo-500/40" style="animation-delay:-1.5s">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200">Catálogo</p>
                    <p class="font-display mt-1 text-lg font-bold">Cursos e palestras</p>
                    <p class="mt-2 text-xs text-indigo-100">Você produz · a câmara agenda</p>
                </div>
                <div class="auth-float absolute left-16 top-36 w-52 rounded-2xl bg-amber-300 p-4 shadow-xl shadow-amber-400/50" style="animation-delay:-3s">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-900/70">Licenças</p>
                    <p class="font-display mt-1 text-lg font-bold text-slate-900">Liberar e cobrar</p>
                </div>
                <div class="auth-float-slow absolute right-4 top-0 hidden w-40 rounded-full bg-white/80 p-3 text-center shadow-lg ring-1 ring-white xl:block" style="animation-delay:-2s">
                    <p class="font-serif text-sm italic text-slate-700">panorama · conteúdo · financeiro</p>
                </div>
            </div>
        </div>

        {{-- Formulário --}}
        <div class="auth-in auth-d5 lg:col-span-5">
            <div class="relative rounded-[1.75rem] bg-white/80 p-6 shadow-2xl shadow-indigo-400/20 ring-1 ring-white backdrop-blur-xl sm:p-8">
                <div class="absolute -top-3 right-8 rounded-full bg-indigo-600 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-lg shadow-indigo-500/40">
                    Diretor
                </div>

                <h2 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">Entrar como diretor</h2>
                <p class="mt-1 text-sm text-slate-500">Acesso exclusivo da direção regional.</p>

                @if (session('status'))
                    <div class="mt-5 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any() || session('error'))
                    <div class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100">
                        {{ $errors->any() ? $errors->first() : session('error') }}
                    </div>
                @endif

                <form action="{{ route('diretor.login.store') }}" method="POST" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">E-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               autocomplete="username"
                               class="auth-field w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 {{ $errors->has('email') ? 'border-red-400' : '' }}"
                               placeholder="diretor@regiao.gov.br" required autofocus />
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label for="password" class="text-xs font-bold uppercase tracking-wider text-slate-500">Senha</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900">Esqueceu?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                   autocomplete="current-password"
                                   class="auth-field w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 {{ $errors->has('password') ? 'border-red-400' : '' }}"
                                   placeholder="••••••••" required />
                            <button type="button" id="toggle-pwd"
                                    class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3.5 text-slate-400 hover:text-slate-700"
                                    aria-label="Mostrar senha">
                                <svg id="pwd-eye" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5 pt-1">
                        <input type="checkbox" name="remember" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-400" />
                        <span class="text-sm text-slate-600">Manter conectado</span>
                    </label>

                    <x-turnstile />
                    @error('cf-turnstile-response')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="auth-btn mt-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-500/30 transition active:scale-[0.98]">
                        Entrar na direção
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </form>

                <p class="mt-6 text-center text-[11px] leading-relaxed text-slate-500">
                    Gestão da câmara?
                    <a href="{{ route('tenant.login') }}" class="font-semibold text-indigo-700 hover:text-indigo-900">Entrar no painel local</a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('toggle-pwd')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        this.setAttribute('aria-label', isHidden ? 'Ocultar senha' : 'Mostrar senha');
        const eye = document.getElementById('pwd-eye');
        eye.innerHTML = isHidden
            ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
    });
</script>
@endpush
@endsection
