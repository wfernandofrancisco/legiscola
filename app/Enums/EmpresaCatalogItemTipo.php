<?php

namespace App\Enums;

enum EmpresaCatalogItemTipo: string
{
    case Produto = 'produto';
    case Servico = 'servico';

    public function label(): string
    {
        return match ($this) {
            self::Produto => 'Produto',
            self::Servico => 'Serviço',
        };
    }
}
