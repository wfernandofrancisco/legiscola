<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InformarExecucaoMoradorRequest extends FormRequest
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
            'servico_finalizado' => ['required', Rule::in(['0', '1'])],
            'execucao_morador_nao_aplica_motivo' => ['required_if:servico_finalizado,0', 'nullable', 'string', 'max:2000'],
        ];
    }
}
