<?php

namespace App\Contracts\Repositories;

use App\Models\EmpresaRelacao;
use App\Models\EmpresaRelacaoArquivo;

interface EmpresaRelacaoArquivoRepositoryInterface
{
    public function create(EmpresaRelacao $relacao, array $attributes): EmpresaRelacaoArquivo;

    public function delete(EmpresaRelacaoArquivo $arquivo): bool;
}
