<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfessorCredenciamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'ano_referencia' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'texto' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'anexos' => ['nullable', 'array'],
            'anexos.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf'],
            'anexo_titulos' => ['nullable', 'array'],
            'anexo_titulos.*' => ['nullable', 'string', 'max:255'],
            'delete_anexos' => ['nullable', 'array'],
            'delete_anexos.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'anexos.*.mimes' => 'Os anexos devem ser enviados somente em PDF.',
            'anexos.*.max' => 'Cada anexo deve ter no máximo 10MB.',
            'ano_referencia.integer' => 'Ano de referência deve ser numérico.',
            'ano_referencia.min' => 'Ano de referência inválido.',
            'ano_referencia.max' => 'Ano de referência inválido.',
        ];
    }
}
