<?php

namespace App\Contracts\Services;

use App\Models\Empresa;

interface EmpresaOverrideServiceInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function applyPayload(Empresa $empresa, int $tenantId, array $payload): void;
}
