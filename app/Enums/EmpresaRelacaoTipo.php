<?php

namespace App\Enums;

enum EmpresaRelacaoTipo: string
{
    case ATENDIMENTO = 'atendimento';
    case FISCALIZACAO = 'fiscalizacao';
    case DOCUMENTACAO = 'documentacao';
    case VISITA = 'visita';
    case LIGACAO = 'ligacao';
    case PENDENCIA = 'pendencia';
    case RETORNO = 'retorno';

    public function label(): string
    {
        return match ($this) {
            self::ATENDIMENTO => 'Atendimento',
            self::FISCALIZACAO => 'Fiscalização',
            self::DOCUMENTACAO => 'Documentação',
            self::VISITA => 'Visita',
            self::LIGACAO => 'Ligação',
            self::PENDENCIA => 'Pendência',
            self::RETORNO => 'Retorno',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $item) => [$item->value => $item->label()])->all();
    }
}
