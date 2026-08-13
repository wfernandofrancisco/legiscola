<?php

namespace App\Contracts\Repositories;

interface TenantRepositoryInterface
{
    public function paginateWithSearch(int $perPage = 15, ?string $search = null);

    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc');

    public function find(int $id);

    public function findWithRelations(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);

    public function countUsers(int $tenantId);

    public function existsBySlug(string $slug): bool;
}
