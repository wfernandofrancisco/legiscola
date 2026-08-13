<?php

namespace App\Contracts\Services\Portal;

interface PortalHomeServiceInterface
{
    /**
     * Payload para view portal.home (chaves estáveis para a Blade).
     *
     * @return array<string, mixed>
     */
    public function homePayload(): array;
}
