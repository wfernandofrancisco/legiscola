<?php

namespace App\Contracts\Services;

use App\Models\EmpresaRelacao;
use App\Models\EmpresaRelacaoArquivo;
use Illuminate\Http\UploadedFile;

interface EmpresaRelacaoArquivoServiceInterface
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function attachMany(EmpresaRelacao $relacao, array $files, int $tenantId, int $userId): void;

    public function delete(EmpresaRelacaoArquivo $arquivo): void;
}
