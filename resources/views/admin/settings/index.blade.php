<x-layouts.admin>
    <x-slot name="title">Configurações</x-slot>

    <x-breadcrumb :items="[
        ['label' => 'Painel', 'href' => route('admin.dashboard')],
        ['label' => 'Configurações']
    ]" />

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Dados institucionais da câmara e identidade visual nos documentos e telas públicas.
        </p>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div class="md:col-span-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Marca no portal público</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Título principal do navbar (ex.: «Legiscola» quando preenchido como Nome da cidade). Deixe em branco para usar o nome do tenant (fantasia, razão ou nome interno).
                </p>
                <x-form.input
                    name="portal_nome_cidade"
                    label="Nome da cidade na marca «Legiscola …»"
                    :value="old('portal_nome_cidade', $tenant->portal_nome_cidade)"
                />
            </div>

            <div class="md:col-span-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Câmara municipal</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Estes dados identificam a câmara nos relatórios PDF e nas telas de acesso ao sistema.
                </p>

                <x-form.input
                    name="nome_camara"
                    label="Nome da câmara"
                    :value="old('nome_camara', $settings->nome_camara)"
                />

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emblema da câmara</label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Usado no cabeçalho de PDFs e telas de login. PNG, JPG ou WebP — até 4&nbsp;MB.
                    </p>
                    @if($settings->logo_prefeitura_path)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ asset('storage/'.$settings->logo_prefeitura_path) }}" alt="" class="h-14 w-auto object-contain border border-gray-200 dark:border-gray-600 rounded bg-white p-1"/>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <input type="checkbox" name="remove_logo_prefeitura" value="1" class="rounded border-gray-300" {{ old('remove_logo_prefeitura') ? 'checked' : '' }}/>
                                Remover emblema atual
                            </label>
                        </div>
                    @endif
                    <input type="file" name="logo_prefeitura" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200"/>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto de capa do portal (home)</label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Imagem de fundo do destaque principal do site público. Recomendado: paisagem, boa luz, sem texto pequeno. PNG, JPG ou WebP — até 8&nbsp;MB. Se não houver foto, o portal usa um fundo só com gradientes.
                    </p>
                    @if($settings->foto_capa_path)
                        <div class="mb-2 flex flex-wrap items-center gap-3">
                            <img src="{{ asset('storage/'.$settings->foto_capa_path) }}" alt="" class="h-24 max-w-full rounded-lg border border-gray-200 object-cover dark:border-gray-600"/>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <input type="checkbox" name="remove_foto_capa" value="1" class="rounded border-gray-300" {{ old('remove_foto_capa') ? 'checked' : '' }}/>
                                Remover foto de capa
                            </label>
                        </div>
                    @endif
                    <input type="file" name="foto_capa" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200"/>
                </div>
            </div>

            <div class="md:col-span-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Cores do portal público</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Gradientes, links e destaques da experiência institucional (hex, ex.: <code class="text-xs">#3b82f6</code>). Deixe em branco para o padrão do sistema.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-form.input
                        name="primary_color"
                        label="Cor primária"
                        :value="old('primary_color', $settings->primary_color)"
                        hint="Destaques e botões principais"
                    />
                    <x-form.input
                        name="secondary_color"
                        label="Cor secundária"
                        :value="old('secondary_color', $settings->secondary_color)"
                        hint="Gradiente e contraste"
                    />
                    <x-form.input
                        name="tertiary_color"
                        label="Cor de apoio"
                        :value="old('tertiary_color', $settings->tertiary_color)"
                        hint="Badges e detalhes"
                    />
                </div>
            </div>

            <div class="md:col-span-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Contato</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input
                        name="whatsapp"
                        label="WhatsApp da câmara"
                        :value="old('whatsapp', $settings->whatsapp)"
                        data-mask="phone"
                    />
                    <x-form.input
                        name="telefone"
                        label="Telefone"
                        :value="old('telefone', $settings->telefone)"
                        data-mask="phone"
                    />
                    <x-form.input
                        name="email"
                        label="E-mail"
                        type="email"
                        :value="old('email', $settings->email)"
                    />
                </div>
            </div>

            <div class="md:col-span-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Endereço</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Este endereço alimenta o <strong>mini-mapa</strong> no rodapé do portal público. Se estiver vazio, o sistema usa o endereço do cadastro do tenant; se o tenant tiver <strong>latitude e longitude</strong> preenchidas, o mapa usa as coordenadas.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input
                        name="cep"
                        label="CEP"
                        data-mask="cep"
                        hint="Ao sair do campo com 8 dígitos, cidade, logradouro, bairro e UF são preenchidos pelo ViaCEP."
                        :value="old('cep', $settings->cep)"
                    />
                    <x-form.input
                        name="uf"
                        label="UF"
                        maxlength="2"
                        :value="old('uf', $settings->uf)"
                    />
                    <div class="md:col-span-2">
                        <x-form.input
                            name="cidade"
                            label="Cidade"
                            :value="old('cidade', $settings->cidade)"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <x-form.input
                            name="logradouro"
                            label="Logradouro"
                            :value="old('logradouro', $settings->logradouro)"
                        />
                    </div>
                    <x-form.input
                        name="numero"
                        label="Número"
                        :value="old('numero', $settings->numero)"
                    />
                    <x-form.input
                        name="bairro"
                        label="Bairro"
                        :value="old('bairro', $settings->bairro)"
                    />
                </div>
            </div>

            <div class="md:col-span-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Redes e funcionamento</h3>
                <x-form.textarea
                    name="horario_funcionamento"
                    label="Horário de funcionamento"
                    rows="3"
                    :value="old('horario_funcionamento', $settings->horario_funcionamento)"
                />
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <x-form.input
                        name="instagram"
                        label="Instagram"
                        :value="old('instagram', $settings->instagram)"
                    />
                    <x-form.input
                        name="x"
                        label="X (Twitter)"
                        :value="old('x', $settings->x)"
                    />
                    <x-form.input
                        name="facebook"
                        label="Facebook"
                        :value="old('facebook', $settings->facebook)"
                    />
                </div>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Salvar configurações</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
