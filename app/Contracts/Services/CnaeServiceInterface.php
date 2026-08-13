<?php

namespace App\Contracts\Services;

interface CnaeServiceInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc');
    public function getCnae(int $id);
    public function createCnae(array $data);
    public function updateCnae(int $id, array $data);
    public function deleteCnae(int $id);
}