<?php

namespace App\Contracts\Repositories;

use App\Models\EmpresaRelacao;

interface EmpresaRelacaoRepositoryInterface
{
    public function create(array $data): EmpresaRelacao;

    public function update(EmpresaRelacao $relacao, array $data): EmpresaRelacao;

    public function delete(EmpresaRelacao $relacao): bool;
}
