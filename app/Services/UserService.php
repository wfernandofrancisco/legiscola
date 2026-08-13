<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Jobs\SendEmailSmtpJob;
use App\Mail\PasswordResetMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService implements UserServiceInterface
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function listUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->when(isset($filters['user_type']), fn ($q) => $q->where('user_type', $filters['user_type']))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['search']), fn ($q) => $q->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('email', 'like', "%{$filters['search']}%");
            }))
            ->latest()
            ->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $role = $data['role'] ?? $data['user_type'] ?? User::TYPE_CLIENTE;
        unset($data['role']);

        /** @var User $user */
        $user = $this->userRepository->create($data);
        $user->assignRole($role);

        dispatch(new SendEmailSmtpJob(new WelcomeMail($user), $user->email, $user->name));

        return $user;
    }

    /**
     * Criar usuário como admin com senha temporária
     */
    public function createUserAsAdmin(int $tenantId, array $data): User
    {
        // Gerar senha temporária
        $temporaryPassword = Str::random(12);

        // Preparar dados
        $userData = [
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($temporaryPassword),
            'user_type' => $data['user_type'] ?? 'tenant_user',
            'status' => $data['status'] ?? 'ativo',
            'email_verified_at' => now(),
        ];

        /** @var User $user */
        $user = $this->userRepository->create($userData);

        // Atribuir role
        $role = $data['role'] ?? 'tenant_user';
        $user->assignRole($role);

        // Enviar email com senha temporária
        Mail::to($user->email)->send(new PasswordResetMail($user, $temporaryPassword));

        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $this->userRepository->update($id, $data);

        $user = $this->userRepository->findOrFail($id);

        // Sincronizar role se fornecida
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }

    public function deleteUser(int $id): void
    {
        $this->userRepository->delete($id);
    }

    public function activateUser(int $id): void
    {
        $this->userRepository->update($id, ['status' => User::STATUS_ATIVO]);
    }

    public function deactivateUser(int $id): void
    {
        $this->userRepository->update($id, ['status' => User::STATUS_INATIVO]);
    }

    public function changeUserType(int $id, string $type): void
    {
        $user = $this->userRepository->findOrFail($id);
        $user->update(['user_type' => $type]);
        $user->syncRoles([$type]);
    }
}
