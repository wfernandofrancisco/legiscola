<?php

namespace App\Contracts\Services;

use App\Models\EmpresaRelacao;

interface EmpresaRelacaoComentarioServiceInterface
{
    public function store(EmpresaRelacao $relacao, string $mensagem, int $tenantId, int $userId): void;
}
