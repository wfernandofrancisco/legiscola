<?php

namespace App\Http\Requests\Api;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'      => ['required', 'string', 'max:255'],
            'descricao'   => ['nullable', 'string'],
            'tenant_id'  => ['nullable', 'integer', 'exists:tenants,id'],
            'subtotal'    => ['required', 'numeric', 'min:0'],
            'desconto'    => ['nullable', 'numeric', 'min:0'],
            'total'       => ['nullable', 'numeric', 'min:0'],
            'status'      => ['nullable', Rule::in([
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
