@extends('layouts.portal')

@section('title', 'Primeiro cadastro')

@php
    $inputClass = 'mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600';
    $labelClass = 'block text-sm font-semibold text-slate-800 dark:text-slate-100';
@endphp

@section('content')
    <x-portal.auth-frame
        title="Primeiro cadastro"
        subtitle="Preencha seus dados. Após enviar, confirme o e-mail que enviaremos para liberar o acesso à área do aluno."
    >
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                <p class="font-semibold">Não foi possível concluir o cadastro.</p>
                <p class="mt-1 text-xs sm:text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <form id="portal-aluno-register-form" method="POST" action="{{ route('tenant.register.store') }}" class="space-y-8">
            @csrf

            <fieldset class="space-y-4">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dados do aluno</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="name">Nome completo</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name"
                               class="{{ $inputClass }} @error('name') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="cpf">CPF</label>
                        <input id="cpf" name="cpf" type="text" inputmode="numeric" autocomplete="off" maxlength="14"
                               data-mask="cpf" required
                               value="{{ old('cpf') }}"
                               class="{{ $inputClass }} @error('cpf') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('cpf')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="birth_date">Data de nascimento</label>
                        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" required
                               class="{{ $inputClass }} @error('birth_date') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('birth_date')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="sexo">Sexo</label>
                        <select id="sexo" name="sexo" required class="{{ $inputClass }} @error('sexo') border-red-400 @enderror">
                            <option value="" disabled {{ old('sexo') ? '' : 'selected' }}>Selecione</option>
                            <option value="masculino" @selected(old('sexo') === 'masculino')>Masculino</option>
                            <option value="feminino" @selected(old('sexo') === 'feminino')>Feminino</option>
                            <option value="outro" @selected(old('sexo') === 'outro')>Outro</option>
                            <option value="nao_informado" @selected(old('sexo') === 'nao_informado')>Prefiro não informar</option>
                        </select>
                        @error('sexo')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="cidade">Cidade</label>
                        <input id="cidade" name="cidade" type="text" value="{{ old('cidade') }}" required
                               class="{{ $inputClass }} @error('cidade') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('cidade')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="email">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                               class="{{ $inputClass }} @error('email') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="password">Senha</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="{{ $inputClass }} @error('password') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('password')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="password_confirmation">Confirmar senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                               class="{{ $inputClass }}"/>
                    </div>
                </div>
            </fieldset>

            @isset($globalPrivacyTerm)
                <fieldset class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/40">
                    <legend class="px-1 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Privacidade (LGPD)</legend>
                    <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $globalPrivacyTerm->title }}</span>
                        <span class="text-slate-500 dark:text-slate-400"> — versão {{ $globalPrivacyTerm->version }}</span>
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                        Leia o texto completo na página
                        <a href="{{ route('portal.privacidade') }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Política de privacidade</a>
                        antes de aceitar.
                    </p>
                    <label class="mt-4 flex cursor-pointer items-start gap-3 text-sm text-slate-800 dark:text-slate-200">
                        <input type="checkbox" name="accept_global_privacy" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400 dark:border-slate-600"
                               @checked(old('accept_global_privacy')) />
                        <span>Declaro que li e aceito a política de privacidade e o tratamento dos meus dados pessoais conforme o termo aplicável à plataforma.</span>
                    </label>
                    @error('accept_global_privacy')
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endisset

            <button type="submit"
                    class="flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                    style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                Enviar cadastro
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
            Já possui cadastro?
            <a href="{{ route('portal.acesso.login') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Entrar</a>
        </p>

        <p class="mt-3 text-center text-sm text-slate-600 dark:text-slate-400">
            Primeira vez no portal?
            <a href="{{ route('portal.tutorial') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Veja o tutorial passo a passo</a>
        </p>
    </x-portal.auth-frame>
@endsection

@push('scripts')
<script>
(function () {
    function onlyDigits(str) {
        return String(str || '').replace(/\D/g, '');
    }
    function formatCpfDigits(d) {
        d = onlyDigits(d).slice(0, 11);
        if (d.length <= 3) return d;
        if (d.length <= 6) return d.slice(0, 3) + '.' + d.slice(3);
        if (d.length <= 9) return d.slice(0, 3) + '.' + d.slice(3, 6) + '.' + d.slice(6);
        return d.slice(0, 3) + '.' + d.slice(3, 6) + '.' + d.slice(6, 9) + '-' + d.slice(9, 11);
    }
    function bindMask(input) {
        if (!input) return;
        input.addEventListener('input', function () {
            input.value = formatCpfDigits(input.value);
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('portal-aluno-register-form');
        if (!form) return;
        var cpf = form.querySelector('[data-mask="cpf"]');
        bindMask(cpf);
        if (cpf && cpf.value) cpf.dispatchEvent(new Event('input'));
    });
})();
</script>
@endpush
