@php
    $certificateTemplate = $certificateTemplate ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST"
    action="{{ $action === 'edit' ? route('admin.templates-certificado.update', $certificateTemplate) : route('admin.templates-certificado.store') }}"
    enctype="multipart/form-data"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Novo Template</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Nome" :required="true" :value="$certificateTemplate?->name ?? old('name')" />
            <x-form.select name="tipo_emissao" label="Tipo de emissão" :required="true"
                :selected="old('tipo_emissao', $certificateTemplate?->tipo_emissao?->value ?? 'curso')"
                :options="\App\Enums\CertificateTipoEmissao::options()" />
            <x-form.select name="engine" label="Engine" :required="true" :selected="$certificateTemplate?->engine ?? old('engine', 'blade')" :options="[
                'blade' => 'Blade',
                'html' => 'HTML',
                'image' => 'Imagem',
            ]" />
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Imagem de fundo</label>
                <input type="file" name="background_image" accept="image/*"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Recomendado para A4 horizontal: <strong>3508 x 2480 px</strong> (300 DPI).
                    Alternativas: <strong>2480 x 1754 px</strong> (150 DPI) ou <strong>1123 x 794 px</strong> (96 DPI).
                </p>
                @if ($certificateTemplate?->background_image_path)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Atual: {{ $certificateTemplate->background_image_path }}
                    </p>
                @endif
                <input type="hidden" name="background_image_path"
                    value="{{ $certificateTemplate?->background_image_path ?? old('background_image_path') }}">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template HTML</label>
                <textarea name="html_template" rows="7"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                    placeholder="Use placeholders: @{{aluno_nome}}, @{{curso_nome}}, @{{evento_nome}}, …">{{ $certificateTemplate?->html_template ?? old('html_template') }}</textarea>
                <div class="mt-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs text-indigo-800 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-200">
                    <p class="font-semibold mb-1">Dicas de placeholders</p>
                    <p><strong>Tipo curso:</strong> <code>@{{curso_nome}}</code>, <code>@{{carga_horaria}}</code>, <code>@{{data_conclusao}}</code>, etc. <strong>Tipo evento:</strong> use <code>@{{evento_nome}}</code> e <code>@{{aluno_nome}}</code> para o participante. <strong>Tipo palestrante:</strong> use <code>@{{palestrante_nome}}</code> (ou <code>@{{aluno_nome}}</code>), <code>@{{evento_nome}}</code> e opcionalmente <code>@{{palestrante_cpf}}</code>.</p>
                    <p class="mt-1">Comuns: <code>@{{aluno_nome}}</code>, <code>@{{cidade}}</code>, <code>@{{uf}}</code>, <code>@{{tenant_nome}}</code>, <code>@{{escola_legislativa}}</code>, <code>@{{hash_validacao}}</code>. No <strong>Testar Template</strong> o PDF usa dados fictícios.</p>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) ($certificateTemplate?->is_active ?? old('is_active', true)))
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    Template ativo
                </label>
            </div>
        </div>
    </fieldset>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit" formtarget="_blank" formmethod="POST" formaction="{{ route('admin.templates-certificado.preview') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 text-indigo-700 dark:text-indigo-300 dark:border-indigo-700 px-5 py-2 text-sm font-medium hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
            Testar Template
        </button>
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition ml-2">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Salvar Template' }}
        </button>
        <a href="{{ route('admin.templates-certificado.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>
