@php
    $tenant = $tenant ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST"
    action="{{ $action === 'create' ? route('central.tenants.store') : route('central.tenants.update', $tenant) }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8 overflow-hidden">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    {{-- Erros --}}
    @if ($errors->any())
        <div
            class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                    clip-rule="evenodd" />
            </svg>
            <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-4">

        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Identificação do cliente</legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="sm:col-span-2">
                    <x-form.input name="name" label="Nome interno / exibição" :required="true" :value="$tenant?->name ?? old('name')" />
                </div>
                <x-form.input name="slug" label="Slug" :required="true" :value="$tenant?->slug ?? old('slug')"
                    hint="Subdomínio / identificador único" />
                <x-form.input name="domain" label="Domínio" :value="$tenant?->domain ?? old('domain')" />
                <x-form.select name="status" label="Status assinatura" :selected="$tenant?->status ?? old('status', 'ativo')" :options="[
                    'ativo' => 'Ativo',
                    'inativo' => 'Inativo',
                    'suspenso' => 'Suspenso',
                ]" />
                <x-form.select name="modulos_plano" label="Plano de módulos (produto)"
                    :selected="old('modulos_plano', $tenant?->modulos_plano?->value ?? \App\Enums\TenantModulosPlano::ADMIN->value)"
                    :options="\App\Enums\TenantModulosPlano::options()"
                    hint="Define se o cliente usa apenas o painel administrativo do tenant ou também o portal web, conforme contrato/licenciamento." />
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Dados cadastrais (CNPJ)</legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="sm:col-span-2">
                    <x-form.input name="razao_social" label="Razão social" :required="true" :value="$tenant?->razao_social ?? old('razao_social')" />
                </div>
                <x-form.input name="nome_fantasia" label="Nome fantasia" :value="$tenant?->nome_fantasia ?? old('nome_fantasia')" />
                <x-form.input name="cnpj" label="CNPJ" :required="true" :value="$tenant?->cnpj ?? old('cnpj')" id="cnpj" />
                <x-form.input name="contact_email" label="E-mail de contato" type="email" :value="$tenant?->contact_email ?? old('contact_email')" />
                <x-form.input name="phone" label="Telefone" :value="$tenant?->phone ?? old('phone')" />
                <x-form.input name="website" label="Site" :value="$tenant?->website ?? old('website')" />
                <x-form.select name="cadastro_status" label="Status do cadastro" :selected="$tenant?->cadastro_status ?? old('cadastro_status', 'pendente')" :options="[
                    'pendente' => 'Pendente',
                    'ativo' => 'Ativo',
                    'inativo' => 'Inativo',
                ]" />
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Endereço</legend>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <x-form.input name="cep" label="CEP" :value="$tenant?->cep ?? old('cep')" id="cep" />
                <div class="col-span-2">
                    <x-form.input name="logradouro" label="Logradouro" :value="$tenant?->logradouro ?? old('logradouro')" />
                </div>
                <x-form.input name="numero" label="Número" :value="$tenant?->numero ?? old('numero')" />
                <x-form.input name="complemento" label="Complemento" :value="$tenant?->complemento ?? old('complemento')" />
                <x-form.input name="bairro" label="Bairro" :value="$tenant?->bairro ?? old('bairro')" />
                <div class="col-span-2">
                    <x-form.input name="cidade" label="Cidade" :value="$tenant?->cidade ?? old('cidade')" />
                </div>
                <x-form.input name="estado" label="UF" maxlength="2" :value="$tenant?->estado ?? old('estado')" />
                <x-form.input name="codigo_ibge_municipio" label="Codigo IBGE do municipio (7 digitos; Receita, etc.)"
                    maxlength="20"
                    :value="$tenant?->codigo_ibge_municipio ?? old('codigo_ibge_municipio')"
                    hint="Codigo da tabela de municipios da base CNPJ da Receita (ex.: Leme/SP = 6635)." />
                <x-form.input name="codigo_municipio_estban" label="Codigo municipio (ESTBAN CO_MUNICIPIO / CODMUN_IBGE)" maxlength="20"
                    :value="$tenant?->codigo_municipio_estban ?? old('codigo_municipio_estban')"
                    hint="Opcional: 7 digitos como no CSV ESTBAN. Se vazio, a importacao usa o codigo IBGE acima." />
                <x-form.input name="codigo_municipio_caged" label="Codigo municipio (microdado Caged)" maxlength="20"
                    :value="$tenant?->codigo_municipio_caged ?? old('codigo_municipio_caged')"
                    hint="Igual à coluna município do arquivo .txt (em geral 6 ou 7 dígitos); pode diferir do codigo da base CNPJ." />
                <x-form.input name="codigo_importacao_exportacao" label="Codigo municipio (Comex CO_MUN)" maxlength="20"
                    :value="$tenant?->codigo_importacao_exportacao ?? old('codigo_importacao_exportacao')"
                    hint="Código IBGE do município de 7 dígitos como na coluna CO_MUN dos CSV de importação/exportação Comex." />
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Centro do mapa (admin — geolocalização)</legend>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Usado na tela <strong class="text-gray-800 dark:text-gray-200">Admin → Mapa de empresas</strong> como zoom inicial (ex.: Leme-SP ≈ lat <code class="text-xs">-22.18535</code>, lng <code class="text-xs">-47.38805</code>).
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <x-form.input name="latitude" label="Latitude" type="text"
                    :value="$tenant?->latitude ?? old('latitude')"
                    hint="Entre -90 e 90 (use ponto decimal)." />
                <x-form.input name="longitude" label="Longitude" type="text"
                    :value="$tenant?->longitude ?? old('longitude')"
                    hint="Entre -180 e 180 (use ponto decimal)." />
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Descrição e observações</legend>
            <div class="pb-6 border-b border-gray-200 dark:border-gray-700 space-y-4">
                <x-form.textarea name="description" label="Descrição" :rows="2" :value="$tenant?->description ?? old('description')" />
                <x-form.textarea name="observacoes" label="Observações" :rows="3" :value="$tenant?->observacoes ?? old('observacoes')" />
            </div>
        </fieldset>

        @if ($action === 'create')
            <fieldset>
                <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Convidar primeiro usuário (opcional)</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <x-form.input name="invite_name_1" label="Nome" :value="old('invite_name_1')" />
                    <x-form.input name="invite_email_1" label="E-mail" type="email" :value="old('invite_email_1')" />
                    <x-form.input name="invite_cargo_1" label="Cargo" :value="old('invite_cargo_1')" />
                </div>
            </fieldset>
        @endif

        {{-- Assinatura --}}
        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Assinatura</legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <x-form.input name="trial_ends_at" label="Fim do Período de Teste" type="datetime-local"
                    :value="$tenant?->trial_ends_at?->format('Y-m-d\TH:i') ?? old('trial_ends_at')" />
                <x-form.input name="subscription_expires_at" label="Expiração da Assinatura" type="datetime-local"
                    :value="$tenant?->subscription_expires_at?->format('Y-m-d\TH:i') ??
                        old('subscription_expires_at')" />
            </div>
        </fieldset>

        {{-- Estatísticas (apenas no edit) --}}
        @if ($action === 'edit' && $tenant)
            <fieldset>
                <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Estatísticas</legend>
                <div class="pb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div
                            class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                        {{ $tenant->budgets_count ?? 0 }}</p>
                                    <p class="text-sm text-blue-600 dark:text-blue-400">Orçamentos</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                                        {{ $tenant->users_count }}</p>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">Usuários</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        @endif

        {{-- Ações --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            {{-- Legenda de campos obrigatórios --}}
            <div class="mb-6 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2">
                <span class="text-red-500 font-bold">*</span>
                <span>Indica campo obrigatório</span>
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 active:bg-indigo-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Salvar
                </button>
                <a href="{{ route('central.tenants.index') }}"
                    class="rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </a>
            </div>
        </div>

    </div>
</form>

<script>
    // Máscara para CNPJ
    document.getElementById('cnpj').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/(\d{2})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        }
    });

    // Busca CEP
    document.getElementById('cep').addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.querySelector('[name="logradouro"]').value = data.logradouro || '';
                        document.querySelector('[name="bairro"]').value = data.bairro || '';
                        document.querySelector('[name="cidade"]').value = data.localidade || '';
                        document.querySelector('[name="estado"]').value = data.uf || '';
                    }
                })
                .catch(error => console.error('Erro ao buscar CEP:', error));
        }
    });
</script>
