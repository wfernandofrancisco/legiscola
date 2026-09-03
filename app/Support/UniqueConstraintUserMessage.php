<?php

namespace App\Support;

use Illuminate\Database\UniqueConstraintViolationException;

final class UniqueConstraintUserMessage
{
    /**
     * @var array<string, array{0: string, 1: string}>
     */
    private const INDEX_MAP = [
        'users_cpf_unique' => ['cpf', 'Este CPF já está cadastrado. Entre na sua conta ou use a recuperação de senha.'],
        'users_email_unique' => ['email', 'Este e-mail já está cadastrado. Entre na sua conta ou use a recuperação de senha.'],
        'students_cpf_unique' => ['cpf', 'Este CPF já está cadastrado. Entre na sua conta ou use a recuperação de senha.'],
        'students_email_unique' => ['email', 'Este e-mail já está cadastrado. Entre na sua conta ou use a recuperação de senha.'],
    ];

    /**
     * @return array{field: string, message: string}
     */
    public static function fromException(UniqueConstraintViolationException $exception): array
    {
        $index = self::extractIndexName($exception);

        if ($index !== null && isset(self::INDEX_MAP[$index])) {
            [$field, $message] = self::INDEX_MAP[$index];

            return ['field' => $field, 'message' => $message];
        }

        if ($index !== null && str_contains($index, 'cpf')) {
            return [
                'field' => 'cpf',
                'message' => 'Este CPF já está cadastrado. Entre na sua conta ou use a recuperação de senha.',
            ];
        }

        if ($index !== null && str_contains($index, 'email')) {
            return [
                'field' => 'email',
                'message' => 'Este e-mail já está cadastrado. Entre na sua conta ou use a recuperação de senha.',
            ];
        }

        return [
            'field' => 'form',
            'message' => 'Já existe um cadastro com estes dados. Verifique e-mail ou CPF e tente novamente.',
        ];
    }

    public static function extractIndexName(UniqueConstraintViolationException $exception): ?string
    {
        $haystack = trim(($exception->errorInfo[2] ?? '').' '.$exception->getMessage());

        if (preg_match("/for key ['`](?:[\w]+\.)?([\w]+)['`]/i", $haystack, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
