<?php

namespace App\Enums;

enum CatalogLicensePagamentoStatus: string
{
    case Pendente = 'pendente';
    case Parcial = 'parcial';
    case Pago = 'pago';
    case Isento = 'isento';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Parcial => 'Parcial',
            self::Pago => 'Pago',
            self::Isento => 'Isento',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pago, self::Isento => 'green',
            self::Parcial => 'yellow',
            self::Pendente => 'blue',
            self::Cancelado => 'gray',
        };
    }

    /**
     * Entra na conta de recebíveis em aberto.
     */
    public function isOpen(): bool
    {
        return $this === self::Pendente || $this === self::Parcial;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
