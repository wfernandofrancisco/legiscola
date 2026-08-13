<?php

namespace App\Contracts\Repositories;

use App\Models\SobreEscola;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SobreEscolaRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?SobreEscola;

    public function create(array $data): SobreEscola;

    public function update(SobreEscola $sobreEscola, array $data): bool;

    public function delete(SobreEscola $sobreEscola): bool;
}
