<?php

namespace App\Http\Requests\App;

use App\Support\OrcamentoAvaliacaoDimensoes;
use Illuminate\Foundation\Http\FormRequest;

class AvaliarOrcamentoSolicitacaoRequest extends FormRequest
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
        $rules = [
            'avaliacao_estrelas' => ['required', 'integer', 'min:1', 'max:5'],
            'avaliacao_comentario' => ['nullable', 'string', 'max:2000'],
            'scores' => ['nullable', 'array'],
        ];

        foreach (array_keys(OrcamentoAvaliacaoDimensoes::labels()) as $key) {
            $rules['scores.'.$key] = ['nullable', 'integer', 'min:1', 'max:5'];
        }

        return $rules;
    }
}
