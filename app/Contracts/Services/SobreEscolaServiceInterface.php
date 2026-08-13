<?php

namespace App\Contracts\Services;

use App\Models\SobreEscola;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SobreEscolaServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): SobreEscola;

    public function update(SobreEscola $sobreEscola, array $data): bool;

    public function delete(SobreEscola $sobreEscola): bool;
}
