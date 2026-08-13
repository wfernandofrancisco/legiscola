<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrcamentoSolicitacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:180'],
            'mensagem' => ['required', 'string', 'max:5000'],
            'empresa_catalog_item_id' => ['nullable', 'integer', 'exists:empresa_catalog_items,id'],
        ];
    }
}
