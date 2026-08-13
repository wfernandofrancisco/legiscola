<?php

namespace App\Enums;

enum EmpresaSituacaoCadastral: string
{
    case NULA = '01';
    case ATIVA = '02';
    case SUSPENSA = '03';
    case INAPTA = '04';
    case BAIXADA = '08';

    public function label(): string
    {
        return match ($this) {
            self::NULA => 'Nula',
            self::ATIVA => 'Ativa',
            self::SUSPENSA => 'Suspensa',
            self::INAPTA => 'Inapta',
            self::BAIXADA => 'Baixada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ATIVA => 'green',
            self::SUSPENSA => 'yellow',
            self::INAPTA, self::BAIXADA => 'red',
            self::NULA => 'gray',
        };
    }

    public static function fromCode(?string $code): ?self
    {
        if ($code === null) {
            return null;
        }

        $normalized = str_pad(preg_replace('/\D/', '', $code) ?? '', 2, '0', STR_PAD_LEFT);

        return self::tryFrom($normalized);
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
