<?php

namespace App\Http\Requests\Responsible;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarParecerDivergenciaRequest extends FormRequest
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
            'execucao_empresa_parecer' => ['required', 'string', 'max:5000'],
        ];
    }
}
