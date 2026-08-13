<?php

namespace App\Http\Requests\Responsible;

use Illuminate\Foundation\Http\FormRequest;

class DefinirAgendamentoEmpresaRequest extends FormRequest
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
            'agendamento_data_hora' => ['required', 'date'],
        ];
    }
}
