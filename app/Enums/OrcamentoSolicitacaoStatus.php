<?php

namespace App\Enums;

enum OrcamentoSolicitacaoStatus: string
{
    case Pendente = 'pendente';
    case Respondido = 'respondido';
    case Fechado = 'fechado';
    case Agendado = 'agendado';
    case Divergente = 'divergente';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Aguardando resposta',
            self::Respondido => 'Em negociação',
            self::Fechado => 'Acordo fechado',
            self::Agendado => 'Agendado',
            self::Divergente => 'Divergência na execução',
            self::Finalizado => 'Execução concluída',
            self::Cancelado => 'Cancelado',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Finalizado, self::Cancelado, self::Divergente], true);
    }
}
