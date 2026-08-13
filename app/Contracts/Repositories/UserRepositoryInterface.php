<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByCpf(string $cpf): ?User;

    public function getByType(string $userType): Collection;

    public function getByStatus(string $status): Collection;

    public function updateLastLogin(int $userId, string $ip): void;
}
