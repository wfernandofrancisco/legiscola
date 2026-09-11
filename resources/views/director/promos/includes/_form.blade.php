@php
    /** @var \App\Models\CatalogPromo|null $promo */
    $isEdit = isset($promo) && $promo !== null;
    $formAction = $isEdit ? route('diretor.promos.update', $promo) : route('diretor.promos.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-8"
    x-data="{ alcance: @js(old('alcance', $promo?->alcance ?? 'geral')) }">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <fieldset>
        <legend class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Conteúdo do aviso</legend>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            O admin vê isso no dashboard. Ao clicar, abre o curso com as aulas.
        </p>

        <div class="grid grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            <div class="sm:col-span-2">
                <x-form.select name="catalog_item_id" label="Curso / palestra do catálogo" required
                    :options="$itens->mapWithKeys(fn ($i) => [$i->id => $i->titulo.' ('.$i->tipo->label().')'])->all()"
                    :selected="old('catalog_item_id', $promo?->catalog_item_id)"
                    placeholder="Selecione um item publicado" />
            </div>

            <div class="sm:col-span-2">
                <x-form.input name="titulo" label="Título do banner" required
                    :value="old('titulo', $promo?->titulo)"
                    hint="Ex.: Curso Processo Legislativo com 20% de desconto" />
            </div>

            <div class="sm:col-span-2">
                <x-form.textarea name="mensagem" label="Mensagem" rows="3"
                    :value="old('mensagem', $promo?->mensagem)"
                    hint="Opcional. Texto curto para complementar o título." />
            </div>

            <x-form.input name="preco_de" label="Preço de (R$)" type="number" step="0.01"
                :value="old('preco_de', $promo?->preco_de)" />
            <x-form.input name="preco_por" label="Preço por (R$)" type="number" step="0.01"
                :value="old('preco_por', $promo?->preco_por)" />
            <x-form.input name="desconto_percentual" label="Desconto (%)" type="number" min="1" max="100"
                :value="old('desconto_percentual', $promo?->desconto_percentual)"
                hint="Opcional. Pode usar % e/ou os preços acima." />
        </div>
    </fieldset>

    <fieldset>
        <legend class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Quem vê</legend>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            Só câmaras <strong>ativas</strong> das suas UFs. Geral atualiza sozinho quando entra câmara nova na região.
        </p>

        <div class="grid grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            <div class="sm:col-span-2">
                <x-form.select name="alcance" label="Alcance" required
                    :options="\App\Models\CatalogPromo::alcanceOptions()"
                    :selected="old('alcance', $promo?->alcance ?? 'geral')"
                    x-model="alcance" />
            </div>

            <div class="sm:col-span-2" x-show="alcance === 'especifico'" x-cloak>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Câmaras</label>
                <div class="max-h-56 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    @php
                        $selectedTenants = collect(old('tenant_ids', $promo?->tenants?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
                    @endphp
                    @forelse ($camaras as $camara)
                        <label class="flex min-h-11 items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <input type="checkbox" name="tenant_ids[]" value="{{ $camara->id }}"
                                @checked($selectedTenants->contains((int) $camara->id))
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-800 dark:text-slate-200">
                                {{ $camara->display_name }}
                                <span class="text-xs text-slate-500">· {{ $camara->estado }}{{ $camara->cidade ? ' / '.$camara->cidade : '' }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">Nenhuma câmara ativa na sua região.</p>
                    @endforelse
                </div>
                @error('tenant_ids')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <x-form.date name="inicia_em" label="Válido a partir de"
                :value="old('inicia_em', $promo?->inicia_em?->format('Y-m-d'))" />
            <x-form.date name="termina_em" label="Válido até"
                :value="old('termina_em', $promo?->termina_em?->format('Y-m-d'))"
                hint="Em branco = sem prazo." />

            <label class="flex items-center gap-2 sm:col-span-2">
                <input type="hidden" name="ativo" value="0">
                <input type="checkbox" name="ativo" value="1"
                    @checked(old('ativo', $promo?->ativo ?? true))
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-700 dark:text-slate-300">Aviso ativo</span>
            </label>
        </div>
    </fieldset>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('diretor.promos.index') }}"
            class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
            Cancelar
        </a>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
            {{ $isEdit ? 'Salvar aviso' : 'Publicar aviso' }}
        </button>
    </div>
</form>
