@php
    /** @var \App\Models\CatalogLicense|null $licenca */
    $isEdit = $licenca !== null;
    $formAction = $isEdit ? route('diretor.licencas.update', $licenca) : route('diretor.licencas.store');
    $tipoInicial = $isEdit
        ? $licenca->catalogItem->tipo->value
        : (
            $itemSelecionado
                ? optional($itens->firstWhere('id', $itemSelecionado))?->tipo?->value
                : null
        );
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-8"
    x-data="{
        tipo: @js(old('tipo_detectado', $tipoInicial)),
        modalidade: @js(old('modalidade', $licenca?->modalidade?->value ?? 'presencial')),
        get ehPalestra() { return this.tipo === 'palestra' },
        get ehCurso() { return this.tipo === 'curso' },
        syncTipoFromSelect() {
            const el = document.getElementById('catalog_item_id');
            if (!el) return;
            const opt = el.options[el.selectedIndex];
            this.tipo = opt?.dataset?.tipo || null;
        }
    }"
    x-init="syncTipoFromSelect()">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <fieldset>
        <legend class="mb-6 text-lg font-semibold text-slate-900 dark:text-white">O que e para quem</legend>

        <div class="grid min-w-0 grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            @if ($isEdit)
                {{-- Trocar item ou câmara seria outra licença, então aqui eles só são exibidos. --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/50">
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Item do catálogo</p>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $licenca->catalogItem->titulo }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $licenca->catalogItem->tipo->label() }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/50">
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Câmara</p>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                        {{ $licenca->tenant->display_name }}</p>
                </div>
            @else
                <div class="select2-wrap min-w-0">
                    <label for="catalog_item_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Item do catálogo <span class="text-red-500">*</span>
                    </label>
                    <select id="catalog_item_id" name="catalog_item_id" required
                        class="js-select2 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        data-placeholder="Buscar item..."
                        x-on:change="syncTipoFromSelect()">
                        <option value=""></option>
                        @foreach ($itens as $item)
                            <option value="{{ $item->id }}" data-tipo="{{ $item->tipo->value }}"
                                @selected((string) old('catalog_item_id', $itemSelecionado) === (string) $item->id)>
                                {{ $item->tipo->label() }} · {{ $item->titulo }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Aparecem apenas os itens publicados do seu catálogo.</p>
                    @error('catalog_item_id')
                        <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="select2-wrap min-w-0">
                    <label for="tenant_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Câmara <span class="text-red-500">*</span>
                    </label>
                    <select id="tenant_id" name="tenant_id" required
                        class="js-select2 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        data-placeholder="Buscar câmara...">
                        <option value=""></option>
                        @foreach ($camaras as $camara)
                            <option value="{{ $camara->id }}"
                                @selected((string) old('tenant_id') === (string) $camara->id)>
                                {{ $camara->display_name }} / {{ $camara->estado }}
                                @if ($camara->cidade) — {{ $camara->cidade }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_id')
                        <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    </fieldset>

    <fieldset>
        <legend class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Regras de uso</legend>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400" x-show="ehCurso" x-cloak>
            A câmara define as datas de cada turma; estes limites valem para todas elas.
        </p>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400" x-show="ehPalestra" x-cloak>
            Você agenda a palestra aqui; a câmara confirma o evento local (inscrições, presença e certificado).
        </p>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400" x-show="!ehCurso && !ehPalestra">
            Escolha o item do catálogo para ver as regras adequadas.
        </p>

        <div class="grid grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            <x-form.select name="status" label="Situação" required :options="\App\Enums\CatalogLicenseStatus::options()"
                :selected="$licenca?->status?->value ?? old('status', 'ativa')"
                hint="Só licença ativa aparece para a câmara." />

            <x-form.date name="exibir_ate" label="Exibir até"
                :value="$licenca?->exibir_ate?->format('Y-m-d') ?? old('exibir_ate')"
                hint="Depois desta data o conteúdo some do painel da câmara. Em branco = sem prazo." />

            <div x-show="ehCurso" x-cloak>
                <x-form.input name="max_turmas" label="Limite de turmas" type="number"
                    :value="$licenca?->max_turmas ?? old('max_turmas')"
                    hint="Quantas turmas a câmara pode abrir com este item. Em branco = ilimitado." />
            </div>

            <div x-show="ehPalestra" x-cloak>
                <x-form.select name="modalidade" label="Modalidade" required
                    :options="\App\Enums\CatalogLicenseModalidade::options()"
                    :selected="old('modalidade', $licenca?->modalidade?->value ?? 'presencial')"
                    x-model="modalidade"
                    hint="Presencial ou online — ambos usam limite de inscritos." />
            </div>

            <div x-show="ehPalestra" x-cloak>
                <x-form.input name="max_inscritos" label="Limite de inscritos" type="number"
                    :value="$licenca?->max_inscritos ?? old('max_inscritos')"
                    hint="Teto de vagas do evento. Em branco = sem limite." />
            </div>

            <div class="sm:col-span-2" x-show="ehPalestra" x-cloak>
                <x-form.input name="palestra_em" label="Data e hora da palestra" type="datetime-local"
                    :value="old('palestra_em', $licenca?->palestra_em?->format('Y-m-d\TH:i'))"
                    hint="Obrigatória no presencial. Entra no calendário da direção regional." />
            </div>

            <div class="sm:col-span-2" x-show="ehCurso || ehPalestra" x-cloak>
                <x-form.input name="professor_nome" label="Professor / palestrante"
                    :value="$licenca?->professor_nome ?? old('professor_nome')"
                    hint="Este nome entra no certificado. Em palestra, também pré-preenche o cadastro do evento." />
            </div>

            <div class="sm:col-span-2">
                <x-form.textarea name="observacoes" label="Observações" rows="3"
                    :value="$licenca?->observacoes ?? old('observacoes')" />
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Financeiro</legend>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            Controle seu, para acompanhar o que foi cobrado e o que já entrou. A câmara não vê estes campos.
        </p>

        <div class="grid grid-cols-1 gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-700">
            <x-form.input name="valor" label="Valor (R$)" type="number" step="0.01"
                :value="$licenca?->valor ?? old('valor')" />

            <x-form.select name="pagamento_status" label="Situação do pagamento" required
                :options="\App\Enums\CatalogLicensePagamentoStatus::options()"
                :selected="$licenca?->pagamento_status?->value ?? old('pagamento_status', 'pendente')" />

            <x-form.date name="vencimento_em" label="Vencimento"
                :value="$licenca?->vencimento_em?->format('Y-m-d') ?? old('vencimento_em')" />

            <x-form.input name="forma_pagamento" label="Forma de pagamento"
                :value="$licenca?->forma_pagamento ?? old('forma_pagamento')"
                hint="Empenho, boleto, PIX, etc." />

            @if ($isEdit)
                <x-form.date name="pago_em" label="Pago em"
                    :value="$licenca?->pago_em?->format('Y-m-d') ?? old('pago_em')"
                    hint="Em branco com situação Pago preenche com a data de hoje." />

                <x-form.input name="nota_fiscal_numero" label="Nota fiscal (número)"
                    :value="$licenca?->nota_fiscal_numero ?? old('nota_fiscal_numero')" />

                <x-form.date name="nota_fiscal_emitida_em" label="Nota fiscal emitida em"
                    :value="$licenca?->nota_fiscal_emitida_em?->format('Y-m-d') ?? old('nota_fiscal_emitida_em')" />
            @endif
        </div>
    </fieldset>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('diretor.licencas.index') }}"
            class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
            Cancelar
        </a>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
            {{ $isEdit ? 'Salvar licença' : 'Liberar para a câmara' }}
        </button>
    </div>
</form>
