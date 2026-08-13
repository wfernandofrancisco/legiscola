<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function listUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function createUser(array $data): User;

    public function createUserAsAdmin(int $tenantId, array $data): User;

    public function updateUser(int $id, array $data): User;

    public function deleteUser(int $id): void;

    public function activateUser(int $id): void;

    public function deactivateUser(int $id): void;

    public function changeUserType(int $id, string $type): void;
}
