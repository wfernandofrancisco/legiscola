<?php

namespace App\Contracts\Repositories;

use App\Models\ProfessorCredenciamento;
use App\Models\ProfessorCredenciamentoAnexo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProfessorCredenciamentoRepositoryInterface
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function findById(int $id): ?ProfessorCredenciamento;

    public function create(array $data): ProfessorCredenciamento;

    public function update(ProfessorCredenciamento $credenciamento, array $data): bool;

    public function delete(ProfessorCredenciamento $credenciamento): bool;

    public function createAnexo(array $data): ProfessorCredenciamentoAnexo;

    public function deleteAnexo(ProfessorCredenciamentoAnexo $anexo): bool;
}
