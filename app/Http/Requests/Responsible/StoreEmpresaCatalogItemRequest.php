<?php

namespace App\Http\Requests\Responsible;

use App\Enums\EmpresaCatalogItemTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmpresaCatalogItemRequest extends FormRequest
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
            'tipo' => ['required', 'string', Rule::enum(EmpresaCatalogItemTipo::class)],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:65000'],
            'preco_base' => ['nullable', 'numeric', 'min:0'],
            'parcelas_maximas' => ['nullable', 'integer', 'min:1', 'max:24'],
            'ativo' => ['sometimes', 'boolean'],
            'aceita_solicitacao_orcamento' => ['sometimes', 'boolean'],
            'foto_principal' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'fotos_galeria' => ['nullable', 'array', 'max:5'],
            'fotos_galeria.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remover_foto_principal' => ['sometimes', 'boolean'],
            'remover_fotos_galeria' => ['sometimes', 'array'],
            'remover_fotos_galeria.*' => ['integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('remover_foto_principal')) {
            $this->merge([
                'remover_foto_principal' => filter_var($this->input('remover_foto_principal'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
