@extends('layouts.guest')

@section('title', 'Sou Munícipe — ' . (isset($tenant) ? ($tenant->display_name ?? $tenant->name) : 'Portal'))

@section('content')
<div class="flex min-h-screen bg-slate-50 lg:h-screen lg:max-h-screen lg:overflow-hidden">

    {{-- ═══ Left panel — branding ═══ --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 flex-col justify-between overflow-y-auto bg-linear-to-b from-emerald-600 via-emerald-700 to-teal-800 px-8 py-8 text-white lg:min-h-0">
        {{-- Branding stack --}}
        <div class="space-y-4">

            {{-- Top bar: DesenvolveCity + botão voltar --}}
            <div class="flex items-center justify-between">
                 <div class="flex items-center gap-2">
                            <img src="{{ asset('img/logo2.png') }}" alt="DesenvolveCity" class="h-8 w-auto opacity-80">
                            <span class="text-2xl font-semibold text-white tracking-wide">DesenvolveCity</span>
                        </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 rounded-lg bg-white/10 px-2.5 py-1 text-xs font-medium text-emerald-100 ring-1 ring-white/15 transition hover:bg-white/20">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Voltar ao portal
                </a>
            </div>

            <div class="h-px bg-white/10"></div>

            {{-- Logos lado a lado: prefeitura + secretaria --}}
            @if(!empty($settings?->logo_prefeitura_path))
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 shadow-inner">
                    <img src="{{ asset('storage/'.$settings->logo_prefeitura_path) }}" alt="Emblema da câmara" class="h-14 w-auto object-contain ">
                </div>
            </div>
            @elseif(isset($tenant))
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 font-extrabold text-xl ring-1 ring-white/20 backdrop-blur">
                {{ $tenant->portalBrandInitials() }}
            </div>
            @endif

            {{-- Tenant name + secretaria --}}
            @isset($tenant)
            <div>
                <p class="text-lg font-extrabold leading-tight">{{ $tenant->portalBrandTitle() }}</p>
                @if(!empty($settings?->nome_camara))
                <p class="mt-0.5 text-xs text-emerald-200">{{ $settings->nome_camara }}</p>
                @endif
            </div>
            @endisset

            <div class="h-px bg-white/15"></div>
        </div>

        {{-- Pitch --}}
        <div class="space-y-4">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-emerald-100">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                    Munícipe
                </div>
                <h1 class="text-2xl font-extrabold leading-snug xl:text-3xl">
                    Participe da economia local
                </h1>
                <p class="mt-2 text-xs leading-relaxed text-emerald-100 xl:text-sm">
                    Crie sua conta de cidadão e solicite orçamentos a empresas do município, acompanhe negociações e apoie o comércio local.
                </p>
            </div>

            <ul class="space-y-2 text-xs xl:text-sm">
                @foreach([
                    'Solicite orçamentos a empresas locais',
                    'Acompanhe propostas em tempo real',
                    'Descubra negócios próximos de você',
                    'Apoie a economia do seu município',
                ] as $item)
                <li class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <svg class="h-3 w-3 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </div>
                    <p class="text-sm text-emerald-100">{{ $item }}</p>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Footer --}}
        <div class="space-y-1.5 text-xs text-emerald-200">
            <p>Já tem conta? <a href="{{ route('tenant.login') }}" class="font-semibold text-white underline-offset-2 hover:underline">Entrar</a></p>
            <p>Responsável por empresa? <a href="{{ route('register.responsavel') }}" class="font-semibold text-white underline-offset-2 hover:underline">Cadastrar empresa</a></p>
            <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                <img src="{{ asset('img/logo2.png') }}" alt="DesenvolveCity" class="h-5 w-auto opacity-60">
                <span class="text-emerald-400 text-[11px]">Tecnologia DesenvolveCity</span>
            </div>
        </div>
    </div>

    {{-- ═══ Right panel — form ═══ --}}
    <div class="flex flex-1 flex-col items-center overflow-y-auto px-4 py-6 sm:px-6 sm:py-8 lg:min-h-0 lg:justify-center lg:py-4 xl:px-8">

        {{-- Mobile header --}}
        <div class="mb-4 flex w-full max-w-4xl items-center justify-between lg:hidden">
            @isset($tenant)
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                @if(!empty($settings?->logo_prefeitura_path))
                <img src="{{ asset('storage/'.$settings->logo_prefeitura_path) }}" alt="Logo" class="h-8 w-auto">
                @else
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow">
                    {{ $tenant->portalBrandInitials() }}
                </div>
                @endif
                <span class="font-bold text-slate-800">{{ $tenant->portalBrandTitle() }}</span>
            </a>
            @endisset
            <a href="{{ route('home') }}" class="text-xs font-medium text-slate-500 hover:text-slate-700">← Portal</a>
        </div>

        {{-- Card --}}
        <div class="w-full max-w-4xl pb-4 lg:pb-0">
            <div class="mb-4 text-center lg:mb-3 lg:text-left">
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 xl:text-3xl">Cadastro de Munícipe</h2>
                <p class="mt-1 text-sm text-slate-500">Preencha os dados para solicitar orçamentos no portal.</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-3 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="post" action="{{ route('register.morador.store') }}" class="space-y-4 lg:space-y-3">
                @csrf
                @php
                    $inputBase = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition-all duration-200 hover:bg-white hover:border-slate-300 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 focus:outline-none';
                    $inputOk   = $inputBase;
                    $inputErr  = $inputBase . ' border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-500/10';
                    $inp = fn($field) => $errors->has($field) ? $inputErr : $inputOk;
                @endphp

                <div class="grid gap-4 md:grid-cols-2 md:items-start md:gap-x-4 md:gap-y-3 lg:gap-x-5">
                {{-- Card: Dados Pessoais --}}
                <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm transition-shadow duration-300 sm:p-5">
                    <div class="mb-3 border-b border-slate-100 pb-2">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800">
                            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Dados Pessoais
                        </h3>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">Nome completo <span class="text-red-500">*</span></label>
                            <input name="name" value="{{ old('name') }}" required class="{{ $inp('name') }}" placeholder="Seu nome completo" />
                            @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">E-mail <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="{{ $inp('email') }}" placeholder="seu@email.com" />
                            @error('email')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">CPF <span class="text-red-500">*</span></label>
                                <input name="cpf" value="{{ old('cpf') }}" required class="{{ $inp('cpf') }}" placeholder="000.000.000-00" />
                                @error('cpf')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">Celular <span class="text-red-500">*</span></label>
                                <input name="phone" value="{{ old('phone') }}" required class="{{ $inp('phone') }}" placeholder="(00) 00000-0000" />
                                @error('phone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Endereço --}}
                <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm transition-shadow duration-300 sm:p-5">
                    <div class="mb-3 border-b border-slate-100 pb-2">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800">
                            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Endereço
                        </h3>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">CEP <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input id="cep" name="cep" value="{{ old('cep') }}" required maxlength="9"
                                       class="{{ $inp('cep') }} pr-10"
                                       placeholder="00000-000" />
                                <div id="cep-spinner" class="pointer-events-none absolute inset-y-0 right-3 hidden items-center">
                                    <svg class="h-4 w-4 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </div>
                            @error('cep')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            <p id="cep-error" class="mt-1 hidden text-xs text-red-600">CEP não encontrado. Preencha o endereço manualmente.</p>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-slate-700">Logradouro <span class="text-red-500">*</span></label>
                                <input id="logradouro" name="logradouro" value="{{ old('logradouro') }}" required class="{{ $inp('logradouro') }}" placeholder="Rua, Av., Travessa..." />
                                @error('logradouro')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">Número <span class="text-red-500">*</span></label>
                                <input id="numero" name="numero" value="{{ old('numero') }}" required class="{{ $inp('numero') }}" placeholder="123" />
                                @error('numero')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">
                                    Complemento
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">Opcional</span>
                                </label>
                                <input id="complemento" name="complemento" value="{{ old('complemento') }}" class="{{ $inputOk }}" placeholder="Apto, Bloco..." />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">Bairro <span class="text-red-500">*</span></label>
                                <input id="bairro" name="bairro" value="{{ old('bairro') }}" required class="{{ $inp('bairro') }}" placeholder="Nome do bairro" />
                                @error('bairro')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-slate-700">Cidade <span class="text-red-500">*</span></label>
                                <input id="cidade" name="cidade" value="{{ old('cidade') }}" required class="{{ $inp('cidade') }}" placeholder="Nome da cidade" />
                                @error('cidade')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700">UF <span class="text-red-500">*</span></label>
                                <input id="uf" name="uf" value="{{ old('uf') }}" required maxlength="2" class="{{ $inp('uf') }} uppercase" placeholder="SP" />
                                @error('uf')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                {{-- Card: Senha --}}
                <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm transition-shadow duration-300 sm:p-5">
                    <div class="mb-3 border-b border-slate-100 pb-2">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800">
                            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"/></svg>
                            Segurança
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">Criar senha <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required class="{{ $inp('password') }}" placeholder="••••••••" />
                            @error('password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">Confirmar senha <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required class="{{ $inputOk }}" placeholder="••••••••" />
                        </div>
                    </div>
                </div>

                {{-- Submit + links --}}
                <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-x-4">
                    <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-center text-xs text-slate-600 sm:justify-start sm:text-left">
                        <span>Já tem conta? <a href="{{ route('tenant.login') }}" class="font-bold text-emerald-700 hover:underline">Entrar</a></span>
                        <span class="hidden sm:inline text-slate-300">|</span>
                        <span>Empresa? <a href="{{ route('register.responsavel') }}" class="font-bold text-emerald-700 hover:underline">Cadastrar empresa</a></span>
                    </div>
                    <button type="submit" class="group flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-linear-to-r from-emerald-600 to-teal-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/25 transition hover:from-emerald-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 sm:w-auto sm:min-w-[200px] lg:min-w-[220px]">
                        Criar minha conta
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const cepInput     = document.getElementById('cep');
    const spinner      = document.getElementById('cep-spinner');
    const cepError     = document.getElementById('cep-error');
    const fields       = {
        logradouro: document.getElementById('logradouro'),
        bairro:     document.getElementById('bairro'),
        cidade:     document.getElementById('cidade'),
        uf:         document.getElementById('uf'),
    };

    // Mask: 00000-000
    cepInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
        this.value = v;
        if (v.replace(/\D/g, '').length === 8) fetchCep(v.replace(/\D/g, ''));
    });

    function fetchCep(cep) {
        spinner.classList.remove('hidden');
        spinner.classList.add('flex');
        cepError.classList.add('hidden');

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(r => r.json())
            .then(data => {
                spinner.classList.add('hidden');
                spinner.classList.remove('flex');

                if (data.erro) {
                    cepError.classList.remove('hidden');
                    return;
                }

                if (data.logradouro) { fields.logradouro.value = data.logradouro; fields.logradouro.readOnly = true; }
                if (data.bairro)     { fields.bairro.value     = data.bairro;     fields.bairro.readOnly     = true; }
                if (data.localidade) { fields.cidade.value     = data.localidade; fields.cidade.readOnly     = true; }
                if (data.uf)         { fields.uf.value         = data.uf;         fields.uf.readOnly         = true; }

                // Focus no número depois do preenchimento automático
                const numeroInput = document.getElementById('numero');
                if (numeroInput) numeroInput.focus();
            })
            .catch(() => {
                spinner.classList.add('hidden');
                spinner.classList.remove('flex');
                cepError.classList.remove('hidden');
            });
    }

    // UF uppercase
    fields.uf.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    // CPF mask: 000.000.000-00
    const cpfInput = document.querySelector('input[name="cpf"]');
    cpfInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 9)      v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
        else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
        else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
        this.value = v;
    });

    // Phone mask: (00) 00000-0000
    const phoneInput = document.querySelector('input[name="phone"]');
    phoneInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 10)     v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
        else if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
        else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
        else if (v.length > 0) v = '('+v;
        this.value = v;
    });

    // Strip masks before submit
    document.querySelector('form').addEventListener('submit', function () {
        cpfInput.value   = cpfInput.value.replace(/\D/g, '');
        phoneInput.value = phoneInput.value.replace(/\D/g, '');
    });
})();
</script>
@endpush
