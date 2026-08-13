<?php

namespace App\Repositories;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    public function paginateWithSearch(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->model
            ->when($search, fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('razao_social', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%");
            }))
            ->withCount(['users'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        return $this->model
            ->when($search, fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('razao_social', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%");
            }))
            ->withCount(['users'])
            ->when($sortBy, fn ($q) => $q->orderBy($sortBy, $sortDir), fn ($q) => $q->orderBy('name'))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateWithFilters(int $perPage = 15, array $filters = [], ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        return $this->model
            ->when($filters['status'] ?? null, fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['search'] ?? null, fn ($q) => $q->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('slug', 'like', "%{$filters['search']}%")
                    ->orWhere('domain', 'like', "%{$filters['search']}%")
                    ->orWhere('razao_social', 'like', "%{$filters['search']}%")
                    ->orWhere('cnpj', 'like', "%{$filters['search']}%");
            }))
            ->withCount(['users'])
            ->when($sortBy, fn ($q) => $q->orderBy($sortBy, $sortDir), fn ($q) => $q->latest())
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findWithRelations(int $id): ?Tenant
    {
        return $this->model->with(['users'])->find($id);
    }

    public function countUsers(int $tenantId): int
    {
        return $this->model->find($tenantId)?->users()->count() ?? 0;
    }

    public function existsBySlug(string $slug): bool
    {
        return $this->model->where('slug', $slug)->exists();
    }
}
