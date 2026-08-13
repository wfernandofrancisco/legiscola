<?php

namespace App\Repositories;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\Budget;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Budget();
    }

    public function getByStatus(string $status): Collection
    {
        return Budget::query()->where('status', $status)->get();
    }

    public function getByTenant(int $tenantId): Collection
    {
        return Budget::query()->where('tenant_id', $tenantId)->get();
    }

    public function getByUser(int $userId): Collection
    {
        return Budget::query()->where('user_id', $userId)->get();
    }
}
