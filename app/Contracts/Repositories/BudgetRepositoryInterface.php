<?php

namespace App\Contracts\Repositories;

interface BudgetRepositoryInterface extends BaseRepositoryInterface
{
    public function getByStatus(string $status): \Illuminate\Database\Eloquent\Collection;
    public function getByTenant(int $tenantId): \Illuminate\Database\Eloquent\Collection;
    public function getByUser(int $userId): \Illuminate\Database\Eloquent\Collection;
}
