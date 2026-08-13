<?php

namespace App\Contracts;

interface TransactionalSmsSenderInterface
{
    /**
     * @param  string  $digits  Somente dígitos (ex.: DDD + número)
     */
    public function send(string $digits, string $message): void;
}
