@php
    /** @var \App\Models\CatalogItem|null $item */
    $isEdit = $item !== null;
    $formAction = $isEdit ? route('diretor.catalogo.update', $item) : route('diretor.catalogo.store');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <fieldset>
        <legend class="mb-6 text-lg font-semibold text-slate-900 dark:text-white">Identificação</legend>

        <div class="grid grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            <x-form.select name="tipo" label="Tipo" required :options="\App\Enums\CatalogItemTipo::options()"
                :selected="$item?->tipo?->value ?? old('tipo', 'curso')"
                hint="Palestra pode virar evento na agenda da câmara." />

            <x-form.select name="status" label="Situação" required :options="\App\Enums\CatalogItemStatus::options()"
                :selected="$item?->status?->value ?? old('status', 'rascunho')"
                hint="Só item publicado pode ser liberado para uma câmara." />

            <div class="sm:col-span-2">
                <x-form.input name="titulo" label="Título" required :value="$item?->titulo ?? old('titulo')" />
            </div>

            <div class="sm:col-span-2">
                <x-form.input name="resumo" label="Resumo" :value="$item?->resumo ?? old('resumo')"
                    hint="Uma linha que aparece na listagem e na oferta para a câmara." />
            </div>

            <div class="sm:col-span-2">
                <x-form.textarea name="descricao" label="Descrição" rows="5"
                    :value="$item?->descricao ?? old('descricao')" />
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend class="mb-6 text-lg font-semibold text-slate-900 dark:text-white">Detalhes</legend>

        <div class="grid grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            <x-form.input name="workload_hours" label="Carga horária (horas)" type="number"
                :value="$item?->workload_hours ?? old('workload_hours')" />

            <x-form.input name="preco_sugerido" label="Preço sugerido (R$)" type="number" step="0.01"
                :value="$item?->preco_sugerido ?? old('preco_sugerido')"
                hint="Referência sua; o valor cobrado é definido em cada licença." />

            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Imagem de
                    capa</label>

                @if ($item?->capa_path)
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ Storage::disk('public')->url($item->capa_path) }}" alt=""
                            class="h-16 w-28 rounded-lg object-cover" />
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <input type="checkbox" name="remove_capa" value="1"
                                class="rounded border-slate-300 text-red-600 focus:ring-red-500" />
                            Remover capa atual
                        </label>
                    </div>
                @endif

                <input type="file" name="capa" accept="image/*"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-300 dark:file:bg-indigo-950/50 dark:file:text-indigo-300" />
                @error('capa')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </fieldset>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ $isEdit ? route('diretor.catalogo.show', $item) : route('diretor.catalogo.index') }}"
            class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
            Cancelar
        </a>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
            {{ $isEdit ? 'Salvar alterações' : 'Criar item' }}
        </button>
    </div>
</form>
