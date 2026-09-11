<?php

namespace App\Http\Requests\Director;

use App\Models\CatalogPromo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogPromoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catalog_item_id' => ['required', 'integer', 'exists:catalog_items,id'],
            'titulo' => ['required', 'string', 'max:160'],
            'mensagem' => ['nullable', 'string', 'max:1000'],
            'preco_de' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'preco_por' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'desconto_percentual' => ['nullable', 'integer', 'min:1', 'max:100'],
            'alcance' => ['required', Rule::in([CatalogPromo::ALCANCE_GERAL, CatalogPromo::ALCANCE_ESPECIFICO])],
            'tenant_ids' => ['nullable', 'array'],
            'tenant_ids.*' => ['integer', 'exists:tenants,id'],
            'inicia_em' => ['nullable', 'date'],
            'termina_em' => ['nullable', 'date', 'after_or_equal:inicia_em'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'catalog_item_id' => 'curso / palestra',
            'titulo' => 'título do aviso',
            'mensagem' => 'mensagem',
            'preco_de' => 'preço de',
            'preco_por' => 'preço por',
            'desconto_percentual' => 'desconto (%)',
            'alcance' => 'alcance',
            'tenant_ids' => 'câmaras',
            'inicia_em' => 'início',
            'termina_em' => 'término',
        ];
    }
}
