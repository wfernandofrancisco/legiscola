<?php

namespace App\Contracts\Repositories;

use App\Models\EmpresaRelacao;
use App\Models\EmpresaRelacaoComentario;

interface EmpresaRelacaoComentarioRepositoryInterface
{
    public function create(EmpresaRelacao $relacao, array $attributes): EmpresaRelacaoComentario;
}
