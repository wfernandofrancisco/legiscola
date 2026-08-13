<?php

namespace App\Http\Requests\Api;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'      => ['sometimes', 'string', 'max:255'],
            'descricao'   => ['nullable', 'string'],
            'tenant_id'  => ['nullable', 'integer', 'exists:tenants,id'],
            'subtotal'    => ['sometimes', 'numeric', 'min:0'],
            'desconto'    => ['nullable', 'numeric', 'min:0'],
            'total'       => ['nullable', 'numeric', 'min:0'],
            'status'      => ['sometimes', Rule::in([
                Budget::STATUS_PENDENTE,
                Budget::STATUS_APROVADO,
                Budget::STATUS_REJEITADO,
                Budget::STATUS_CANCELADO,
            ])],
            'validade'    => ['nullable', 'date', 'after_or_equal:today'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo'      => 'título',
            'descricao'   => 'descrição',
            'tenant_id'  => 'cliente',
            'subtotal'    => 'subtotal',
            'desconto'    => 'desconto',
            'total'       => 'total',
            'status'      => 'status',
            'validade'    => 'validade',
            'observacoes' => 'observações',
        ];
    }
}
