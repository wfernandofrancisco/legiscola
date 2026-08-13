<?php

namespace App\Contracts\Repositories;

interface CnaeRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc');
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}