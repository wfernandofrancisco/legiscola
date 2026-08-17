<?php

namespace App\Enums;

enum CertificateTipoEmissao: string
{
    case Curso = 'curso';
    case Evento = 'evento';
    case Palestrante = 'palestrante';

    public function label(): string
    {
        return match ($this) {
            self::Curso => 'Curso (turmas)',
            self::Evento => 'Evento',
            self::Palestrante => 'Palestrante',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
