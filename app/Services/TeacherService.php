<?php

namespace App\Services;

use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Services\TeacherServiceInterface;
use App\Models\Teacher;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeacherService implements TeacherServiceInterface
{
    public function __construct(private TeacherRepositoryInterface $teacherRepository) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->teacherRepository->paginateFiltered($perPage, $search);
    }

    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data): Teacher {
            $tenantId = TenantContext::getTenantId();
            $email = strtolower(trim((string) $data['email']));
            $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? '')) ?: null;

            if ($this->teacherRepository->findByEmail($email)) {
                throw ValidationException::withMessages([
                    'email' => 'Já existe professor com este e-mail.',
                ]);
            }

            $existingUser = User::query()->where('email', $email)->first();
            if ($existingUser) {
                throw ValidationException::withMessages([
                    'email' => 'Este e-mail já está cadastrado em usuários.',
                ]);
            }

            $user = User::query()->create([
                'tenant_id' => $tenantId,
                'name' => $data['full_name'],
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make(Str::random(12)),
                'user_type' => User::TYPE_TENANT_RESPONSIBLE,
                'status' => $data['status'],
            ]);
            $user->syncRoles(['tenant_professor']);

            $teacher = $this->teacherRepository->create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'email' => $email,
                'phone' => $phone,
                'photo_path' => $data['photo_path'] ?? null,
                'status' => $data['status'],
                'bio' => $data['bio'] ?? null,
                'specialities' => $data['specialities'] ?? null,
            ]);

            DB::afterCommit(function () use ($user): void {
                $token = Password::createToken($user);
                $user->sendPasswordResetNotification($token);
            });

            return $teacher;
        });
    }

    public function update(Teacher $teacher, array $data): bool
    {
        return DB::transaction(function () use ($teacher, $data): bool {
            $email = strtolower(trim((string) $data['email']));
            $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? '')) ?: null;

            if ($this->teacherRepository->findByEmail($email, $teacher->id)) {
                throw ValidationException::withMessages([
                    'email' => 'Já existe professor com este e-mail.',
                ]);
            }

            $existsOnUsers = User::query()
                ->where('email', $email)
                ->where('id', '!=', $teacher->user_id)
                ->exists();

            if ($existsOnUsers) {
                throw ValidationException::withMessages([
                    'email' => 'Este e-mail já está em uso por outro usuário.',
                ]);
            }

            $teacher->user?->update([
                'name' => $data['full_name'],
                'email' => $email,
                'phone' => $phone,
                'status' => $data['status'],
                'user_type' => User::TYPE_TENANT_RESPONSIBLE,
            ]);
            $teacher->user?->assignRole('tenant_professor');

            return $this->teacherRepository->update($teacher, [
                'full_name' => $data['full_name'],
                'email' => $email,
                'phone' => $phone,
                'photo_path' => $data['photo_path'] ?? $teacher->photo_path,
                'status' => $data['status'],
                'bio' => $data['bio'] ?? null,
                'specialities' => $data['specialities'] ?? null,
            ]);
        });
    }

    public function delete(Teacher $teacher): bool
    {
        return $this->teacherRepository->delete($teacher);
    }
}
