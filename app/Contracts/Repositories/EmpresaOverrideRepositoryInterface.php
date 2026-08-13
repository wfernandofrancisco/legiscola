<?php

namespace App\Contracts\Repositories;

use App\Models\EmpresaOverride;

interface EmpresaOverrideRepositoryInterface
{
    public function firstOrNewForEmpresa(int $empresaId, int $tenantId): EmpresaOverride;

    public function persist(EmpresaOverride $override): void;
}
