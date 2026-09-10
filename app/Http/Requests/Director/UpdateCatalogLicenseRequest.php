<?php

namespace App\Http\Requests\Director;

use App\Enums\CatalogItemTipo;
use App\Enums\CatalogLicenseModalidade;
use App\Enums\CatalogLicensePagamentoStatus;
use App\Enums\CatalogLicenseStatus;
use App\Models\CatalogLicense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Na edição o item e a câmara ficam travados: mudar isso seria outra licença, não a mesma.
 */
class UpdateCatalogLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(CatalogLicenseStatus::class)],
            'exibir_ate' => ['nullable', 'date'],
            'max_turmas' => ['nullable', 'integer', 'min:1', 'max:999'],
            'modalidade' => ['nullable', Rule::enum(CatalogLicenseModalidade::class)],
            'palestra_em' => ['nullable', 'date'],
            'max_inscritos' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'professor_nome' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:2000'],

            'valor' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'pagamento_status' => ['required', Rule::enum(CatalogLicensePagamentoStatus::class)],
            'vencimento_em' => ['nullable', 'date'],
            'pago_em' => ['nullable', 'date'],
            'forma_pagamento' => ['nullable', 'string', 'max:100'],
            'nota_fiscal_numero' => ['nullable', 'string', 'max:100'],
            'nota_fiscal_emitida_em' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var CatalogLicense $licenca */
            $licenca = $this->route('licenca');
            if (! $licenca || $licenca->catalogItem->tipo !== CatalogItemTipo::Palestra) {
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
            'pago_em' => 'data do pagamento',
            'forma_pagamento' => 'forma de pagamento',
            'nota_fiscal_numero' => 'número da nota fiscal',
            'nota_fiscal_emitida_em' => 'emissão da nota fiscal',
        ];
    }
}
