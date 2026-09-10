<?php

namespace App\Http\Requests\Director;

use App\Enums\CatalogItemTipo;
use App\Enums\CatalogLicenseModalidade;
use App\Enums\CatalogLicensePagamentoStatus;
use App\Enums\CatalogLicenseStatus;
use App\Models\CatalogItem;
use App\Support\DirectorContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCatalogLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Só item publicado do próprio diretor pode ser liberado.
            'catalog_item_id' => [
                'required',
                Rule::exists('catalog_items', 'id')
                    ->where('owner_user_id', $this->user()->id)
                    ->where('status', 'publicado')
                    ->whereNull('deleted_at'),
            ],
            // E só para câmara dentro da abrangência do diretor.
            'tenant_id' => [
                'required',
                Rule::in(DirectorContext::tenantIds()),
                Rule::unique('catalog_licenses', 'tenant_id')
                    ->where('catalog_item_id', $this->input('catalog_item_id')),
            ],
            'status' => ['required', Rule::enum(CatalogLicenseStatus::class)],
            'exibir_ate' => ['nullable', 'date', 'after_or_equal:today'],
            'max_turmas' => ['nullable', 'integer', 'min:1', 'max:999'],
            'modalidade' => ['nullable', Rule::enum(CatalogLicenseModalidade::class)],
            'palestra_em' => ['nullable', 'date'],
            'max_inscritos' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'professor_nome' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:2000'],

            'valor' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'pagamento_status' => ['nullable', Rule::enum(CatalogLicensePagamentoStatus::class)],
            'vencimento_em' => ['nullable', 'date'],
            'forma_pagamento' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $item = CatalogItem::query()->find($this->integer('catalog_item_id'));
            if (! $item || $item->tipo !== CatalogItemTipo::Palestra) {
                return;
            }

            if (! $this->filled('modalidade')) {
                $validator->errors()->add('modalidade', 'Informe se a palestra é presencial ou online.');
            }

            if ($this->input('modalidade') === CatalogLicenseModalidade::Presencial->value
                && ! $this->filled('palestra_em')) {
                $validator->errors()->add('palestra_em', 'Informe a data da palestra presencial.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'catalog_item_id' => 'item do catálogo',
            'tenant_id' => 'câmara',
            'status' => 'situação',
            'exibir_ate' => 'exibir até',
            'max_turmas' => 'limite de turmas',
            'modalidade' => 'modalidade',
            'palestra_em' => 'data da palestra',
            'max_inscritos' => 'limite de inscritos',
            'professor_nome' => 'professor / palestrante',
            'observacoes' => 'observações',
            'valor' => 'valor',
            'pagamento_status' => 'situação do pagamento',
            'vencimento_em' => 'vencimento',
            'forma_pagamento' => 'forma de pagamento',
        ];
    }

    public function messages(): array
    {
        return [
            'catalog_item_id.exists' => 'Escolha um item publicado do seu catálogo.',
            'tenant_id.in' => 'Esta câmara não está na sua região.',
            'tenant_id.unique' => 'Esta câmara já tem uma licença deste item — edite a licença existente.',
        ];
    }

    public function catalogItem(): CatalogItem
    {
        return CatalogItem::findOrFail($this->integer('catalog_item_id'));
    }
}
