<?php

namespace App\Enums;

enum EmpresaRelacaoCanal: string
{
    case PRESENCIAL = 'presencial';
    case TELEFONE = 'telefone';
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case SISTEMA = 'sistema';

    public function label(): string
    {
        return match ($this) {
            self::PRESENCIAL => 'Presencial',
            self::TELEFONE => 'Telefone',
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'E-mail',
            self::SISTEMA => 'Sistema',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $item) => [$item->value => $item->label()])->all();
    }
}
