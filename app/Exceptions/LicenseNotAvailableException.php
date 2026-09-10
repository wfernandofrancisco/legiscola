<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A licença existe, mas não pode ser usada agora (suspensa, vencida ou no limite de turmas).
 */
class LicenseNotAvailableException extends RuntimeException
{
    public static function naoUtilizavel(): self
    {
        return new self('Esta licença não está ativa no momento. Fale com a direção regional.');
    }

    public static function prazoVencido(string $data): self
    {
        return new self("O acesso a este conteúdo terminou em {$data}. Fale com a direção regional para renovar.");
    }

    public static function limiteDeTurmas(int $max): self
    {
        return new self("Você já abriu o máximo de {$max} turma(s) permitido nesta licença.");
    }

    public static function limiteDeEdicoes(int $max): self
    {
        return new self("Você já agendou o máximo de {$max} edição(ões) permitido nesta licença.");
    }

    public static function foraDoTenant(): self
    {
        return new self('Esta licença não pertence à sua câmara.');
    }

    public static function tipoIncompativel(string $esperado): self
    {
        return new self("Este conteúdo não é do tipo {$esperado}.");
    }
}
