<?php

namespace App\Http\Requests\Responsible;

use Illuminate\Foundation\Http\FormRequest;

class CancelarOrcamentoEmpresaRequest extends FormRequest
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
            'cancelamento_motivo' => ['required', 'string', 'max:128'],
            'cancelamento_comentario' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
