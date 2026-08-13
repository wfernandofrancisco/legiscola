@extends('layouts.guest')

@section('title', 'Recuperar senha — ' . (isset($tenant) ? ($tenant->display_name ?? $tenant->name) : 'Portal'))

@section('content')
<div class="flex h-screen overflow-hidden bg-slate-50">

    {{-- ═══ Left panel — branding ═══ --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 flex-col justify-between overflow-y-auto bg-linear-to-b from-blue-700 via-blue-800 to-blue-900 px-10 py-12 text-white">
        {{-- Branding stack --}}
        <div class="space-y-4">

            {{-- Top bar: DesenvolveCity + botão voltar --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    
                    @if(!empty($settings?->logo_prefeitura_path))
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 shadow-inner">
                            <img src="{{ asset('img/logo.png') }}" alt="Emblema da legiscola" class="h-14 w-auto object-contain ">
                        </div>
                        <div class="flex items-center justify-center rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 shadow-inner">
                            
                            <img src="{{ asset('storage/'.$settings->logo_prefeitura_path) }}" alt="Emblema da câmara" class="h-14 w-auto object-contain ">
                        </div>
                        
                    </div>
                    
                    @elseif(isset($tenant))
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 font-extrabold text-xl ring-1 ring-white/20 backdrop-blur">
                        {{ $tenant->portalBrandInitials() }}
                    </div>
                    @else
                    <div class="flex items-center justify-center rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 shadow-inner">
                        <img src="{{ asset('img/logo.png') }}" alt="Legiscola" class="h-14 w-auto object-contain">
                    </div>
                    @endif
                    
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 rounded-lg bg-white/10 px-2.5 py-1 text-xs font-medium text-blue-100 ring-1 ring-white/15 transition hover:bg-white/20">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Voltar ao portal
                </a>
                
            </div>

            <p class="text-lg font-extrabold leading-tight">Escola Legislativa{{ filled($settings?->nome_camara) ? ' - '.$settings->nome_camara : '' }}</p>

            <div class="h-px bg-white/15"></div>
        </div>

        {{-- Pitch --}}
        <div class="space-y-6">
            <div>
                <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Segurança
                </div>
                <h1 class="text-3xl font-extrabold leading-snug">
                    Recupere o<br>acesso à sua<br>conta
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-blue-200">
                    Informe seu e-mail cadastrado e enviaremos um link seguro para redefinir sua senha.
                </p>
            </div>

            <ul class="space-y-3.5">
                @foreach([
                    'Link válido por 60 minutos',
                    'Verifique também o spam/lixo eletrônico',
                    'Sua senha atual permanece ativa até você alterá-la',
                ] as $item)
                <li class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-400/20">
                        <svg class="h-3.5 w-3.5 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </div>
                    <p class="text-sm text-blue-100">{{ $item }}</p>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Footer --}}
        <div class="space-y-1.5 text-xs text-blue-300">
            <p>Lembrou a senha? <a href="{{ route('tenant.login') }}" class="font-semibold text-white underline-offset-2 hover:underline">Entrar na conta</a></p>
            <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                <img src="{{ asset('img/logo.png') }}" alt="Escola Legislativa" class="h-5 w-auto opacity-60">
                <span class="text-blue-400 text-[11px]">Escola Legislativa{{ filled($settings?->nome_camara) ? ' - '.$settings->nome_camara : '' }}</span>
            </div>
        </div>
    </div>

    {{-- ═══ Right panel ═══ --}}
    <div class="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-12 sm:px-10">

        {{-- Mobile header --}}
        <div class="mb-8 flex w-full max-w-md items-center justify-between lg:hidden">
            @isset($tenant)
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                @if(!empty($settings?->logo_prefeitura_path))
                <img src="{{ asset('storage/'.$settings->logo_prefeitura_path) }}" alt="Logo" class="h-8 w-auto">
                @else
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-700 text-sm font-bold text-white shadow">{{ $tenant->portalBrandInitials() }}</div>
                @endif
                <span class="font-bold text-slate-800">{{ $tenant->portalBrandTitle() }}</span>
            </a>
            @endisset
            <a href="{{ route('tenant.login') }}" class="text-xs font-medium text-slate-500 hover:text-slate-700">← Login</a>
        </div>

        <div class="w-full max-w-md">
            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Recuperação de acesso</p>
                <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Esqueceu sua senha?</h2>
                <p class="mt-1 text-sm text-slate-500">Informe seu e-mail e enviaremos um link para redefinir.</p>
            </div>

            {{-- Status --}}
            @if (session('status'))
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Card --}}
            <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 border-b border-slate-100 pb-4">
                    <h3 class="flex items-center gap-2 text-base font-bold text-slate-800">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                        Seu e-mail cadastrado
                    </h3>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">E-mail <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 bg-slate-50 focus:border-blue-500 focus:ring-blue-500/10' }} px-4 py-3 text-sm text-slate-900 placeholder-slate-400 transition-all duration-200 hover:bg-white hover:border-slate-300 focus:bg-white focus:ring-4 focus:outline-none"
                               placeholder="seu@email.com" />
                        @error('email')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <x-turnstile />
                    @error('cf-turnstile-response')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="group flex w-full items-center justify-center gap-3 rounded-2xl bg-linear-to-r from-blue-700 to-blue-600 px-6 py-4 text-base font-bold text-white shadow-lg shadow-blue-600/30 transition-all duration-300 hover:from-blue-800 hover:to-blue-700 hover:shadow-xl hover:-translate-y-0.5 active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-blue-500/50">
                        Enviar link de recuperação
                        <svg class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="mt-6 text-center text-sm text-slate-500">
                Lembrou a senha? <a href="{{ route('tenant.login') }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">Entrar na conta</a>
            </div>
        </div>
    </div>
</div>
@endsection
