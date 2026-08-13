<?php

namespace App\Contracts\Services;

use App\Models\ProfessorCredenciamento;
use App\Models\ProfessorCredenciamentoAnexo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProfessorCredenciamentoServiceInterface
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function create(array $data): ProfessorCredenciamento;

    public function update(ProfessorCredenciamento $credenciamento, array $data): bool;

    public function delete(ProfessorCredenciamento $credenciamento): bool;

    public function deleteAnexo(ProfessorCredenciamentoAnexo $anexo): bool;
}
