<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreSobreEscolaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institucional' => ['nullable', 'string'],
            'objetivos' => ['nullable', 'string'],
            'quem_somos' => ['nullable', 'string'],
            'projeto_pedagogico' => ['nullable', 'string'],
            'legislacao' => ['nullable', 'string'],
            'eixo_titulos' => ['nullable', 'array'],
            'eixo_titulos.*' => ['nullable', 'string', 'max:255'],
            'eixo_descricoes' => ['nullable', 'array'],
            'eixo_descricoes.*' => ['nullable', 'string'],
            'pessoa_nomes' => ['nullable', 'array'],
            'pessoa_nomes.*' => ['nullable', 'string', 'max:255'],
            'pessoa_cargos' => ['nullable', 'array'],
            'pessoa_cargos.*' => ['nullable', 'string', 'max:255'],
            'pessoa_fotos' => ['nullable', 'array'],
            'pessoa_fotos.*' => ['nullable', 'image', 'max:2048'],
            'delete_pessoas' => ['nullable', 'array'],
            'delete_pessoas.*' => ['nullable', 'integer'],
        ];
    }
}
