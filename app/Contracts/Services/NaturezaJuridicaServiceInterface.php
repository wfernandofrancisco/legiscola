<?php

namespace App\Contracts\Services;

interface NaturezaJuridicaServiceInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc');
    public function getNaturezaJuridica(int $id);
    public function createNaturezaJuridica(array $data);
    public function updateNaturezaJuridica(int $id, array $data);
    public function deleteNaturezaJuridica(int $id);
}