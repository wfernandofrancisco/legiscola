<?php

namespace App\Contracts\Services;

use App\Models\Empresa;
use App\Models\EmpresaRelacao;

interface EmpresaRelacaoServiceInterface
{
    public function create(Empresa $empresa, array $data, int $tenantId, int $userId, array $files = []): EmpresaRelacao;

    public function update(EmpresaRelacao $relacao, array $data, int $tenantId, int $userId): EmpresaRelacao;

    public function addComment(EmpresaRelacao $relacao, string $mensagem, int $tenantId, int $userId): void;

    public function addFiles(EmpresaRelacao $relacao, array $files, int $tenantId, int $userId): void;

    public function delete(EmpresaRelacao $relacao): void;
}
