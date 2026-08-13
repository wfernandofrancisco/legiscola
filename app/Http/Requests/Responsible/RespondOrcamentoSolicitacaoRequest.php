<?php

namespace App\Http\Requests\Responsible;

use App\Enums\OrcamentoPagamentoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondOrcamentoSolicitacaoRequest extends FormRequest
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
            'resposta_mensagem' => ['required', 'string', 'max:5000'],
            'resposta_valor' => ['nullable', 'string', 'max:32'],
            'pagamento_instrucoes' => ['nullable', 'string', 'max:2000'],
            'pagamento_status' => ['nullable', 'string', Rule::in(array_map(fn ($c) => $c->value, OrcamentoPagamentoStatus::cases()))],
            'fechar_solicitacao' => ['nullable', 'boolean'],
        ];
    }
}
