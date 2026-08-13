<?php

namespace App\Contracts\Services;

use App\Models\Tenant;
use App\Models\User;

interface TenantServiceInterface
{
    public function paginate(int $perPage = 15, ?string $search = null);

    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc');

    public function getTenant(int $id): Tenant;

    public function createTenant(array $data): Tenant;

    public function updateTenant(int $id, array $data): Tenant;

    public function deleteTenant(int $id): bool;

    public function linkUserToTenant(int $tenantId, int $userId, ?string $cargo = null, bool $isPrimary = true): void;

    public function unlinkUserFromTenant(int $tenantId, int $userId): void;
}
