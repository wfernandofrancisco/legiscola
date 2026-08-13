<?php

namespace App\Rules;

use App\Support\Turnstile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TurnstileRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Turnstile::isConfigured()) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('Confirme que não é um robô.');

            return;
        }

        if (! Turnstile::verify($value, request()->ip())) {
            $fail('A verificação de segurança falhou. Atualize a página e tente novamente.');
        }
    }
}
