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

        <form id="portal-aluno-register-form" method="POST" action="{{ route('tenant.register.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <fieldset class="space-y-4">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Conta</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="name">Nome completo</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name"
                               class="{{ $inputClass }} @error('name') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
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

            <fieldset class="space-y-4">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dados pessoais</legend>
                <div class="grid gap-4 sm:grid-cols-2">
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
                        <label class="{{ $labelClass }}" for="cpf">CPF</label>
                        <input id="cpf" name="cpf" type="text" inputmode="numeric" autocomplete="off" maxlength="14"
                               data-mask="cpf"
                               value="{{ old('cpf') }}"
                               class="{{ $inputClass }} @error('cpf') border-red-400 ring-2 ring-red-100 @enderror"/>
                        @error('cpf')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="escolaridade">Escolaridade</label>
                        <select id="escolaridade" name="escolaridade" required class="{{ $inputClass }} @error('escolaridade') border-red-400 @enderror">
                            <option value="" disabled {{ old('escolaridade') ? '' : 'selected' }}>Selecione</option>
                            @foreach ($escolaridadeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('escolaridade') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('escolaridade')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="profissao">Profissão <span class="font-normal text-slate-500">(opcional)</span></label>
                        <input id="profissao" name="profissao" type="text" value="{{ old('profissao') }}" maxlength="255"
                               class="{{ $inputClass }}"/>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Contato e endereço</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}" for="telefone">Telefone <span class="font-normal text-slate-500">(opcional)</span></label>
                        <input id="telefone" name="telefone" type="text" inputmode="tel" maxlength="15" data-mask="telefone"
                               value="{{ old('telefone') }}" class="{{ $inputClass }}"/>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="celular">Celular <span class="font-normal text-slate-500">(opcional)</span></label>
                        <input id="celular" name="celular" type="text" inputmode="tel" maxlength="16" data-mask="celular"
                               value="{{ old('celular') }}" class="{{ $inputClass }}"/>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="cep">CEP</label>
                        <input id="cep" name="cep" type="text" inputmode="numeric" maxlength="9" data-mask="cep" data-viacep
                               value="{{ old('cep') }}" placeholder="00000-000"
                               class="{{ $inputClass }}"/>
                        <p class="mt-1 text-xs text-slate-500" data-cep-hint></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="logradouro">Logradouro</label>
                        <input id="logradouro" name="logradouro" type="text" value="{{ old('logradouro') }}"
                               class="{{ $inputClass }}"/>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="numero">Número</label>
                        <input id="numero" name="numero" type="text" value="{{ old('numero') }}"
                               class="{{ $inputClass }}"/>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="bairro">Bairro</label>
                        <input id="bairro" name="bairro" type="text" value="{{ old('bairro') }}"
                               class="{{ $inputClass }}"/>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="cidade">Cidade</label>
                        <input id="cidade" name="cidade" type="text" value="{{ old('cidade') }}"
                               class="{{ $inputClass }}"/>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="uf">UF</label>
                        <input id="uf" name="uf" type="text" maxlength="2" value="{{ old('uf') }}" placeholder="SP"
                               data-uppercase
                               class="{{ $inputClass }} uppercase"/>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Foto de perfil <span class="font-normal">(opcional)</span></legend>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="shrink-0">
                        <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 dark:border-slate-600 dark:bg-slate-900">
                            <img id="portal-aluno-photo-preview" src="" alt="" class="hidden h-full w-full object-cover"/>
                            <span id="portal-aluno-photo-placeholder" class="px-3 text-center text-xs text-slate-500 dark:text-slate-400">Pré-visualização</span>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp,image/jpg"
                               class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-800 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-100"/>
                        @error('photo')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG, PNG ou WebP. Máximo 2&nbsp;MB.</p>
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
    function formatCepDigits(d) {
        d = onlyDigits(d).slice(0, 8);
        if (d.length <= 5) return d;
        return d.slice(0, 5) + '-' + d.slice(5, 8);
    }
    function formatPhoneDigits(d) {
        d = onlyDigits(d).slice(0, 11);
        if (d.length === 0) return '';
        if (d.length <= 2) return '(' + d;
        if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
        if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6, 10);
        return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7, 11);
    }
    function bindMask(input, kind) {
        if (!input) return;
        input.addEventListener('input', function () {
            var raw = onlyDigits(input.value);
            if (kind === 'cpf') input.value = formatCpfDigits(raw);
            else if (kind === 'cep') input.value = formatCepDigits(raw);
            else if (kind === 'telefone' || kind === 'celular') input.value = formatPhoneDigits(raw);
        });
    }
    function bindUppercase(input) {
        if (!input) return;
        input.addEventListener('input', function () {
            input.value = String(input.value || '').toUpperCase().replace(/[^A-Z]/g, '').slice(0, 2);
        });
    }
    function bindPhotoPreview(fileInput, img, placeholder) {
        if (!fileInput || !img) return;
        fileInput.addEventListener('change', function () {
            var f = fileInput.files && fileInput.files[0];
            if (!f) {
                img.classList.add('hidden');
                img.removeAttribute('src');
                if (placeholder) placeholder.classList.remove('hidden');
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                img.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(f);
        });
    }
    function bindViaCep(cepInput, hint) {
        if (!cepInput) return;
        var log = document.getElementById('logradouro');
        var bai = document.getElementById('bairro');
        var cid = document.getElementById('cidade');
        var uf = document.getElementById('uf');
        cepInput.addEventListener('blur', function () {
            var c = onlyDigits(cepInput.value);
            if (c.length !== 8) {
                if (hint) hint.textContent = '';
                return;
            }
            if (hint) hint.textContent = 'Buscando endereço…';
            fetch('https://viacep.com.br/ws/' + c + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.erro) {
                        if (hint) hint.textContent = 'CEP não encontrado.';
                        return;
                    }
                    if (log && !log.value) log.value = data.logradouro || '';
                    if (bai && !bai.value) bai.value = data.bairro || '';
                    if (cid && !cid.value) cid.value = data.localidade || '';
                    if (uf && !uf.value) uf.value = (data.uf || '').toUpperCase();
                    if (hint) hint.textContent = 'Endereço preenchido via CEP.';
                })
                .catch(function () {
                    if (hint) hint.textContent = 'Não foi possível consultar o CEP agora.';
                });
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('portal-aluno-register-form');
        if (!form) return;
        bindMask(form.querySelector('[data-mask="cpf"]'), 'cpf');
        bindMask(form.querySelector('[data-mask="cep"]'), 'cep');
        bindMask(form.querySelector('[data-mask="telefone"]'), 'telefone');
        bindMask(form.querySelector('[data-mask="celular"]'), 'celular');
        bindUppercase(form.querySelector('[data-uppercase]'));
        var cepEl = form.querySelector('[data-viacep]');
        bindViaCep(cepEl, cepEl ? cepEl.closest('div').querySelector('[data-cep-hint]') : null);
        bindPhotoPreview(
            document.getElementById('photo'),
            document.getElementById('portal-aluno-photo-preview'),
            document.getElementById('portal-aluno-photo-placeholder')
        );
        ['cpf', 'cep', 'telefone', 'celular'].forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (el && el.value) el.dispatchEvent(new Event('input'));
        });
    });
})();
</script>
@endpush
