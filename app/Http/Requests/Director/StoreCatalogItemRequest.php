<?php

namespace App\Http\Requests\Director;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(CatalogItemTipo::class)],
            'titulo' => ['required', 'string', 'max:255'],
            'resumo' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:10000'],
            'workload_hours' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'status' => ['required', Rule::enum(CatalogItemStatus::class)],
            'preco_sugerido' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'capa' => ['nullable', 'image', 'max:4096'],
            'remove_capa' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo' => 'tipo',
            'titulo' => 'título',
            'resumo' => 'resumo',
            'descricao' => 'descrição',
            'workload_hours' => 'carga horária',
            'status' => 'situação',
            'preco_sugerido' => 'preço sugerido',
            'capa' => 'imagem de capa',
        ];
    }
}
