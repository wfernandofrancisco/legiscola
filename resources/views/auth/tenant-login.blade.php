@extends('layouts.guest')

@php
    $brandName = filled($settings?->nome_camara)
        ? $settings->nome_camara
        : (isset($tenant) ? ($tenant->display_name ?? $tenant->name) : config('app.name'));
    $logoSrc = file_exists(public_path('img/logo.png'))
        ? asset('img/logo.png')
        : asset('img/legiscola.svg');
@endphp

@section('title', 'Entrar — ' . $brandName)

@section('content')
<div class="flex min-h-full">

    {{-- Painel institucional --}}
    <aside class="auth-ink relative hidden w-[46%] overflow-hidden text-[#f4efe6] lg:flex xl:w-[44%]">
        <div class="auth-grid pointer-events-none absolute inset-0"></div>
        <div class="auth-grain pointer-events-none absolute inset-0"></div>
        <div class="pointer-events-none absolute -left-24 top-24 h-80 w-80 rounded-full bg-amber-700/10 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 right-0 h-72 w-72 rounded-full bg-sky-900/40 blur-3xl"></div>
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
                <a href="{{ route('home') }}" class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-100/70 transition hover:text-amber-50">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Portal
                </a>
            </div>

            <div class="max-w-md">
                <p class="auth-animate auth-d2 mb-5 inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-200/80">
                    <span class="h-px w-8 bg-amber-200/70" aria-hidden="true"></span>
                    Área do administrador
                </p>
                <h1 class="auth-animate auth-d3 font-display text-[2.35rem] font-semibold leading-[1.12] tracking-tight text-white xl:text-[2.7rem]">
                    O painel da sua<br>
                    <em class="font-medium not-italic text-amber-100/90">Escola Legislativa.</em>
                </h1>
                @if(filled($settings?->nome_camara))
                    <p class="auth-animate auth-d3 mt-4 text-sm font-medium text-amber-100/70">{{ $settings->nome_camara }}</p>
                @endif
                <p class="auth-animate auth-d4 mt-5 max-w-sm text-[15px] leading-relaxed text-slate-300/85">
                    Cursos, inscrições, frequência e certificação — com o rigor que a gestão pública exige.
                </p>

                <ol class="auth-animate auth-d5 mt-10 space-y-4 border-t border-white/10 pt-8">
                    @foreach([
                        ['01', 'Cursos e atividades'],
                        ['02', 'Alunos e inscrições'],
                        ['03', 'Frequência e turmas'],
                        ['04', 'Certificados e diplomas'],
                    ] as [$n, $label])
                        <li class="flex items-baseline gap-4">
                            <span class="font-display text-sm italic text-amber-200/70">{{ $n }}</span>
                            <span class="text-sm font-medium tracking-wide text-slate-100/90">{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <p class="auth-animate auth-d5 text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400/80">
                {{ config('app.name') }} · acesso restrito à câmara
            </p>
        </div>
    </aside>

    {{-- Formulário --}}
    <section class="relative flex flex-1 flex-col justify-center px-6 py-12 sm:px-10 lg:px-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgb(196_165_116/0.09),_transparent_55%)]"></div>

        <div class="relative mx-auto w-full max-w-[26rem]">
            <div class="mb-10 flex items-center justify-between lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="h-9 w-auto object-contain">
                    @isset($tenant)
                        <span class="font-display text-base font-semibold text-[#001823]">{{ $tenant->display_name ?? $tenant->name }}</span>
                    @endisset
                </a>
                <a href="{{ route('home') }}" class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500 hover:text-[#001823]">Portal</a>
            </div>

            <p class="auth-animate auth-d1 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-800/70">Bem-vindo de volta</p>
            <h2 class="auth-animate auth-d2 font-display mt-2 text-3xl font-semibold tracking-tight text-[#001823]">Entrar no painel</h2>
            <p class="auth-animate auth-d3 mt-2 text-sm leading-relaxed text-stone-600">
                Use o e-mail da gestão da câmara. Alunos e docentes entram pelo portal do município.
            </p>

            @if ($errors->any() || session('error'))
                <div class="auth-animate mt-6 flex items-start gap-3 rounded-2xl border border-red-200/80 bg-red-50/90 px-4 py-3.5 text-sm text-red-900">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>{{ $errors->any() ? $errors->first() : session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('tenant.login.store') }}" method="POST" class="auth-animate auth-d4 mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-600">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           autocomplete="username"
                           class="auth-input w-full rounded-xl border border-stone-300/80 bg-white/80 px-4 py-3.5 text-sm text-[#001823] outline-none transition placeholder:text-stone-400 {{ $errors->has('email') ? 'border-red-400' : '' }}"
                           placeholder="gestao@camara.gov.br" required autofocus />
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="password" class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-600">Senha</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-stone-500 underline decoration-stone-300 underline-offset-4 transition hover:text-[#001823] hover:decoration-[#001823]">Esqueceu a senha?</a>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               autocomplete="current-password"
                               class="auth-input w-full rounded-xl border border-stone-300/80 bg-white/80 px-4 py-3.5 pr-12 text-sm text-[#001823] outline-none transition placeholder:text-stone-400 {{ $errors->has('password') ? 'border-red-400' : '' }}"
                               placeholder="••••••••" required />
                        <button type="button" id="toggle-pwd"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3.5 text-stone-400 transition hover:text-[#001823]"
                                aria-label="Mostrar senha">
                            <svg id="pwd-eye" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex cursor-pointer items-center gap-2.5 pt-1">
                    <input type="checkbox" name="remember"
                           class="h-4 w-4 cursor-pointer rounded border-stone-300 text-[#001823] focus:ring-[#001823]/20" />
                    <span class="text-sm text-stone-600">Manter-me conectado neste aparelho</span>
                </label>

                <x-turnstile />
                @error('cf-turnstile-response')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="group mt-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-[#001823] px-6 py-3.5 text-sm font-semibold text-[#f4efe6] shadow-[0_12px_30px_-12px_rgba(0,24,35,0.55)] transition hover:bg-[#01293a] active:scale-[0.99]">
                    Entrar no painel
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>

            <p class="mt-10 border-t border-stone-300/60 pt-6 text-center text-xs leading-relaxed text-stone-500">
                Acesso exclusivo da gestão da câmara.<br>
                Professor e aluno usam o portal da escola no subdomínio do município.
            </p>
        </div>
    </section>
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
