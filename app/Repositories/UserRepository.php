<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByCpf(string $cpf): ?User
    {
        return User::where('cpf', $cpf)->first();
    }

    public function getByType(string $userType): Collection
    {
        return User::where('user_type', $userType)->get();
    }

    public function getByStatus(string $status): Collection
    {
        return User::where('status', $status)->get();
    }

    public function updateLastLogin(int $userId, string $ip): void
    {
        User::where('id', $userId)->update([
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Paginate users by tenant with filters and optional sorting
     */
    public function paginateByTenant(
        int $tenantId,
        int $perPage = 1,
        ?string $search = null,
        ?string $status = null,
        ?string $userType = null,
        ?string $sortBy = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = $this->model
            ->where('tenant_id', $tenantId)
            ->when($search, fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($userType, fn ($q) => $q->where('user_type', $userType));

        // Ordenação
        $allowedSorts = ['name', 'email', 'status', 'user_type', 'created_at'];
        if ($sortBy && in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest('created_at');
        }

        return $query->paginate($perPage);
    }
}

