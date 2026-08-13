<?php

namespace App\Services;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Contracts\Services\BudgetServiceInterface;
use App\Models\Budget;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BudgetService implements BudgetServiceInterface
{
    public function __construct(private BudgetRepositoryInterface $budgetRepository)
    {
    }

    public function listBudgets(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Budget::query()
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['tenant_id']), fn ($q) => $q->where('tenant_id', $filters['tenant_id']))
            ->when(isset($filters['user_id']), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->when(isset($filters['search']), fn ($q) => $q->where(function ($query) use ($filters) {
                $query->where('titulo', 'like', "%{$filters['search']}%")
                      ->orWhere('descricao', 'like', "%{$filters['search']}%");
            }))
            ->with(['tenant', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function createBudget(array $data, int $createdByUserId): Budget
    {
        $data['user_id'] = $createdByUserId;
        $data['status']  = $data['status'] ?? Budget::STATUS_PENDENTE;
        $data['total']   = $data['total'] ?? ($data['subtotal'] ?? 0) - ($data['desconto'] ?? 0);

        /** @var Budget $budget */
        $budget = $this->budgetRepository->create($data);

        return $budget;
    }

    public function updateBudget(int $id, array $data): Budget
    {
        if (isset($data['subtotal']) || isset($data['desconto'])) {
            $existing          = $this->budgetRepository->findOrFail($id);
            $subtotal          = $data['subtotal'] ?? $existing->subtotal;
            $desconto          = $data['desconto'] ?? $existing->desconto;
            $data['total']     = $subtotal - $desconto;
        }

        $this->budgetRepository->update($id, $data);

        return $this->budgetRepository->findOrFail($id);
    }

    public function deleteBudget(int $id): void
    {
        $this->budgetRepository->delete($id);
    }

    public function approveBudget(int $id): void
    {
        $this->budgetRepository->update($id, ['status' => Budget::STATUS_APROVADO]);
    }

    public function rejectBudget(int $id): void
    {
        $this->budgetRepository->update($id, ['status' => Budget::STATUS_REJEITADO]);
    }
}
