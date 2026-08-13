<?php

namespace App\Enums;

enum OrcamentoPagamentoStatus: string
{
    case NaoAplicavel = 'nao_aplicavel';
    case Pendente = 'pendente';
    case Informado = 'informado';
    case Confirmado = 'confirmado';

    public function label(): string
    {
        return match ($this) {
            self::NaoAplicavel => 'Não aplicável',
            self::Pendente => 'Pagamento pendente',
            self::Informado => 'Instruções enviadas',
            self::Confirmado => 'Confirmado',
        };
    }
}
