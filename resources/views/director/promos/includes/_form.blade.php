@php
    /** @var \App\Models\CatalogPromo|null $promo */
    $isEdit = isset($promo) && $promo !== null;
    $formAction = $isEdit ? route('diretor.promos.update', $promo) : route('diretor.promos.store');
    $capasItens = $itens->mapWithKeys(fn ($i) => [
        $i->id => $i->capa_path ? Storage::disk('public')->url($i->capa_path) : null,
    ]);
    $capaPropria = $promo?->capa_path ? Storage::disk('public')->url($promo->capa_path) : null;
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data"
    class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_22rem]"
    x-data="{
        alcance: @js(old('alcance', $promo?->alcance ?? 'geral')),
        titulo: @js(old('titulo', $promo?->titulo ?? '')),
        mensagem: @js(old('mensagem', $promo?->mensagem ?? '')),
        catalogItemId: @js((string) old('catalog_item_id', $promo?->catalog_item_id ?? '')),
        capasItens: @js($capasItens),
        capaPropria: @js($capaPropria),
        capaPreview: @js($capaPropria),
        removeCapa: false,
        get capaExibida() {
            if (this.removeCapa) {
                return this.capasItens[this.catalogItemId] || null;
            }
            return this.capaPreview || this.capasItens[this.catalogItemId] || null;
        },
        previewArquivo(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;
            this.removeCapa = false;
            const reader = new FileReader();
            reader.onload = (e) => { this.capaPreview = e.target.result; };
            reader.readAsDataURL(file);
        }
    }">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-8">
        <fieldset class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <legend class="mb-1 text-lg font-semibold text-slate-900 dark:text-white">Comunicado</legend>
            <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                O admin da câmara vê isso num popup ao entrar no painel. Capa, título e texto aparecem juntos.
            </p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.select name="catalog_item_id" label="Curso / palestra do catálogo" required
                        :options="$itens->mapWithKeys(fn ($i) => [$i->id => $i->titulo.' ('.$i->tipo->label().')'])->all()"
                        :selected="old('catalog_item_id', $promo?->catalog_item_id)"
                        placeholder="Selecione um item publicado"
                        x-model="catalogItemId" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.input name="titulo" label="Título do aviso" required
                        :value="old('titulo', $promo?->titulo)"
                        hint="Uma frase forte. Ex.: É a hora de crescer"
                        x-model="titulo" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.textarea name="mensagem" label="Texto do comunicado" rows="8"
                        :value="old('mensagem', $promo?->mensagem)"
                        hint="Quebras de linha são respeitadas. Até 2.500 caracteres."
                        class="min-h-[12rem] resize-y text-base leading-7"
                        x-model="mensagem" />
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Capa do aviso</label>
                    <p class="mb-3 text-xs text-slate-500">JPG ou PNG, até 4 MB. Se não enviar, usa a capa do curso.</p>

                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-amber-400 hover:bg-amber-50/40 dark:border-slate-600 dark:bg-slate-800/60 dark:hover:border-amber-500">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">Anexar imagem de capa</span>
                        <span class="mt-1 text-xs text-slate-500">Arraste ou clique para escolher</span>
                        <input type="file" name="capa" accept="image/*" class="sr-only"
                            @change="previewArquivo($event)">
                    </label>
                    @error('capa')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @if ($promo?->capa_path)
                        <label class="mt-3 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <input type="checkbox" name="remove_capa" value="1"
                                class="rounded border-slate-300 text-red-600 focus:ring-red-500"
                                @change="removeCapa = $event.target.checked">
                            Remover capa atual e voltar à capa do curso
                        </label>
                    @endif
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

        <fieldset class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <legend class="mb-1 text-lg font-semibold text-slate-900 dark:text-white">Quem vê</legend>
            <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                Só câmaras ativas das suas UFs. Geral atualiza sozinho quando entra câmara nova na região.
            </p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
    </div>

    <aside class="xl:sticky xl:top-6 h-fit">
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Como o admin vê</p>
        <div class="overflow-hidden rounded-[1.75rem] border border-amber-200/80 bg-[#fffaf3] shadow-xl dark:border-amber-700/40 dark:bg-slate-900">
            <div class="relative h-40 bg-gradient-to-br from-amber-500 via-orange-400 to-amber-700">
                <template x-if="capaExibida">
                    <img :src="capaExibida" alt="" class="absolute inset-0 h-full w-full object-cover">
                </template>
                <div class="absolute inset-0 bg-gradient-to-t from-[#fffaf3] via-transparent to-black/20 dark:from-slate-900"></div>
            </div>
            <div class="px-5 pb-6 pt-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">
                    Comunicado regional
                </p>
                <h2 class="mt-2 font-serif text-2xl leading-tight text-slate-900 dark:text-white"
                    x-text="titulo || 'Título do comunicado'"></h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                    x-text="mensagem || 'O texto do aviso aparece aqui, com as quebras de linha que você escrever.'"></p>
            </div>
        </div>
    </aside>
</form>
