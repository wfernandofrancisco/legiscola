<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class AprovarAgendamentoMoradorRequest extends FormRequest
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
            'agendamento_cep' => ['required', 'string', 'max:16'],
            'agendamento_logradouro' => ['required', 'string', 'max:191'],
            'agendamento_numero' => ['required', 'string', 'max:32'],
            'agendamento_complemento' => ['nullable', 'string', 'max:191'],
            'agendamento_bairro' => ['required', 'string', 'max:120'],
            'agendamento_cidade' => ['required', 'string', 'max:120'],
            'agendamento_uf' => ['required', 'string', 'size:2'],
            'agendamento_observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
