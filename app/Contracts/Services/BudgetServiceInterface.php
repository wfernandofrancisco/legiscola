<?php

namespace App\Contracts\Services;

use App\Models\Budget;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BudgetServiceInterface
{
    public function listBudgets(array $filters, int $perPage): LengthAwarePaginator;
    public function createBudget(array $data, int $createdByUserId): Budget;
    public function updateBudget(int $id, array $data): Budget;
    public function deleteBudget(int $id): void;
    public function approveBudget(int $id): void;
    public function rejectBudget(int $id): void;
}
