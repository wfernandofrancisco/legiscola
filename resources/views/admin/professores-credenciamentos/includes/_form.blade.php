@php
    $credenciamento = $credenciamento ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST"
    action="{{ $action === 'edit' ? route('admin.professores-credenciamentos.update', $credenciamento) : route('admin.professores-credenciamentos.store') }}"
    enctype="multipart/form-data"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 p-4">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-2">Verifique os erros abaixo:</p>
            <ul class="list-disc ml-5 text-sm text-red-700 dark:text-red-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="titulo" label="Título" :required="true"
            :value="$credenciamento?->titulo ?? old('titulo', 'DOCENTES')" />
        <x-form.input name="ano_referencia" label="Ano de referência" type="number" min="2000" max="2100"
            :value="$credenciamento?->ano_referencia ?? old('ano_referencia', now()->year)" />
        <div class="md:col-span-2">
            <x-form.textarea name="texto" label="Texto público" rows="9" :required="true"
                :value="$credenciamento?->texto ?? old('texto')" />
        </div>
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" @checked((bool) ($credenciamento?->is_active ?? old('is_active', true)))
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                Conteúdo ativo no portal
            </label>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-700 p-5 bg-gray-50/40 dark:bg-gray-900/20">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Anexos</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Envie apenas arquivos <strong>PDF</strong> (máximo 10MB cada).</p>
        @if ($action === 'edit' && $credenciamento?->anexos?->count())
            <div class="mb-4 space-y-2">
                @foreach ($credenciamento->anexos as $anexo)
                    <label class="flex items-center justify-between gap-3 text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                        <span>{{ $anexo->titulo }}</span>
                        <span class="flex items-center gap-3">
                            <a href="{{ asset('storage/' . $anexo->arquivo_path) }}" target="_blank"
                                class="text-indigo-600 dark:text-indigo-300 hover:underline">Abrir</a>
                            <span class="inline-flex items-center gap-1">
                                <input type="checkbox" name="delete_anexos[]" value="{{ $anexo->id }}"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="text-xs text-gray-500">Excluir</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        @endif

        @for ($i = 0; $i < 4; $i++)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <x-form.input name="anexo_titulos[]" label="Título do anexo {{ $i + 1 }}"
                    :value="old('anexo_titulos.' . $i)" />
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Arquivo {{ $i + 1 }}</label>
                    <input type="file" name="anexos[]" accept="application/pdf"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" />
                </div>
            </div>
        @endfor
    </div>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Salvar Conteúdo' }}
        </button>
        <a href="{{ route('admin.professores-credenciamentos.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>
