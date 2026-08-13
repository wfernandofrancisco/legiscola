@extends('layouts.guest')

@section('title', 'Entrar — ' . (isset($tenant) ? ($tenant->display_name ?? $tenant->name) : 'Portal'))

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
        <div class="space-y-8">
            <div>
                <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/>
                    </svg>
                    Painel da Câmara	
                </div>
                <h1 class="text-3xl font-extrabold leading-snug">
                    Acesse o<br>painel da<br>sua câmara
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-blue-200">
                    Gerencie a legislação e a educação legislativa da sua câmara.
                </p>
            </div>

            <ul class="space-y-3.5">
                @foreach([
                    'Gestão de Cursos e Atividades',
                    'Gestão de Alunos e Inscrições',
                    'Gestão de Frequências',
                    'Gestão de Certificados e Diploma',
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
          
            <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                <img src="{{ asset('img/logo.png') }}" alt="Escola Legislativa" class="h-5 w-auto opacity-60">
                <span class="text-blue-400 text-[11px]">Escola Legislativa{{ filled($settings?->nome_camara) ? ' - '.$settings->nome_camara : '' }}</span>
            </div>
        </div>
    </div>

    {{-- ═══ Right panel — form ═══ --}}
    <div class="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-12 sm:px-10 rounded-2xl lg:w-7/12 xl:w-3/5">

        <div class="bg-white/90 px-10 py-8 rounded-2xl shadow-lg w-full max-w-md">
        
        {{-- Mobile header --}}
        <div class="mb-8 flex w-full max-w-md items-center justify-between lg:hidden">
            @isset($tenant)
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-700 text-sm font-bold text-white shadow">
                    {{ strtoupper(substr($tenant->display_name ?? $tenant->name, 0, 2)) }}
                </div>
                <span class="font-bold text-slate-800">{{ $tenant->display_name ?? $tenant->name }}</span>
            </a>
            @endisset
            <a href="{{ route('home') }}" class="text-xs font-medium text-slate-500 hover:text-slate-700">← Portal</a>
        </div>

        {{-- Card --}}
        <div class="w-full max-w-md">
            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Bem-vindo de volta</p>
                <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Entrar na sua conta</h2>
                <p class="mt-1 text-sm text-slate-500">Acesse o painel da sua empresa no portal.</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('tenant.login.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-700">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-xl bg-white px-4 py-3 text-sm text-slate-900 shadow-xs outline-none transition-all duration-200 placeholder:text-slate-400 border {{ $errors->has('email') ? 'border-red-400 focus:border-red-400 focus:ring-3 focus:ring-red-100' : 'border-slate-200 focus:border-blue-400 focus:ring-3 focus:ring-blue-100' }}"
                           placeholder="empresa@email.com" required autofocus />
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Senha --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="password" class="text-xs font-semibold text-slate-700">Senha</label>
                        <a href="{{ route('password.request') }}"
                           class="text-xs font-medium text-blue-600 transition-colors hover:text-blue-800">Esqueceu a senha?</a>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               class="w-full rounded-xl bg-white px-4 py-3 pr-12 text-sm text-slate-900 shadow-xs outline-none transition-all duration-200 placeholder:text-slate-400 border {{ $errors->has('password') ? 'border-red-400 focus:border-red-400 focus:ring-3 focus:ring-red-100' : 'border-slate-200 focus:border-blue-400 focus:ring-3 focus:ring-blue-100' }}"
                               placeholder="••••••••" required />
                        <button type="button" id="toggle-pwd"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3.5 text-slate-400 transition-colors hover:text-slate-600"
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

                {{-- Remember --}}
                <label class="flex cursor-pointer items-center gap-2.5">
                    <input type="checkbox" name="remember"
                           class="h-4 w-4 cursor-pointer rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-400" />
                    <span class="text-sm text-slate-600">Manter-me conectado</span>
                </label>

                <x-turnstile />
                @error('cf-turnstile-response')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Submit --}}
                <button type="submit"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-linear-to-r from-blue-600 to-sky-500 px-6 py-3 text-sm font-bold text-white shadow-md transition-all duration-200 hover:from-blue-700 hover:to-sky-600 hover:shadow-lg hover:shadow-blue-200/60 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    Entrar no painel
                </button>
            </form>

            {{-- Divider --}}
           

            {{-- Secondary actions --}}
           
        </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('toggle-pwd').addEventListener('click', function () {
        const input = document.getElementById('password');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        const eye = document.getElementById('pwd-eye');
        eye.innerHTML = isHidden
            ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
    });
</script>
@endpush
@endsection
