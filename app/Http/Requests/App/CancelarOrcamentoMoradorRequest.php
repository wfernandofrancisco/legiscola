<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class CancelarOrcamentoMoradorRequest extends FormRequest
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
            'cancelamento_motivo' => ['nullable', 'string', 'max:128'],
            'cancelamento_comentario' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
