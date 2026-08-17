<?php

namespace App\Services;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Models\Student;
use App\Models\User;
use App\Support\NominatimGeocoder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentService implements StudentServiceInterface
{
    public function __construct(protected StudentRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Student
    {
        return $this->repository->findById($id);
    }

    public function findByUserId(int $userId): ?Student
    {
        return $this->repository->findByUserId($userId);
    }

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $email = strtolower(trim((string) $data['email']));
            $name = trim((string) ($data['name'] ?? 'Aluno'));
            $password = (string) ($data['password'] ?? '');
            $userCreated = false;
            $passwordProvided = $password !== '';

            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $user = User::query()->create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($passwordProvided ? $password : Str::random(12)),
                    'user_type' => User::TYPE_TENANT_USER,
                    'status' => User::STATUS_ATIVO,
                    'cpf' => $data['cpf'] ?? null,
                ]);
                $user->assignRole('tenant_user');
                $userCreated = true;
            }

            $alreadyLinked = $this->repository->findByUserId((int) $user->id);
            if ($alreadyLinked) {
                throw ValidationException::withMessages([
                    'email' => 'Já existe um aluno vinculado a este e-mail/usuário.',
                ]);
            }

            $data['user_id'] = $user->id;
            $data['email'] = $email;
            $data['status'] = $data['status'] ?? 'ativo';
            if (empty($data['enrollment_number'])) {
                $data['enrollment_number'] = sprintf('ADM-%d-%d', (int) $user->tenant_id, $user->id);
            }
            unset($data['name'], $data['password'], $data['password_confirmation']);

            $data = $this->mergeGeocoding(null, $data);

            $student = $this->repository->create($data);

            if ($userCreated && ! $passwordProvided) {
                DB::afterCommit(function () use ($user): void {
                    $token = Password::createToken($user);
                    $user->sendPasswordResetNotification($token);
                });
            }

            return $student;
        });
    }

    public function update(Student $student, array $data): bool
    {
        return DB::transaction(function () use ($student, $data): bool {
            $email = strtolower(trim((string) $data['email']));
            $name = trim((string) ($data['name'] ?? $student->user?->name ?? 'Aluno'));
            $password = (string) ($data['password'] ?? '');
            $data['email'] = $email;
            unset($data['name'], $data['password'], $data['password_confirmation']);

            $userUpdate = [
                'name' => $name,
                'email' => $email,
            ];
            if ($password !== '') {
                $userUpdate['password'] = Hash::make($password);
            }
            if (array_key_exists('cpf', $data)) {
                $userUpdate['cpf'] = $data['cpf'];
            }
            $student->user?->update($userUpdate);

            $data = $this->mergeGeocoding($student, $data);

            return $this->repository->update($student, $data);
        });
    }

    public function delete(Student $student): bool
    {
        return $this->repository->delete($student);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergeGeocoding(?Student $existing, array $data): array
    {
        $addressKeys = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'uf'];
        $touched = false;
        foreach ($addressKeys as $key) {
            if (array_key_exists($key, $data)) {
                $touched = true;
                break;
            }
        }

        if (! $touched) {
            return $data;
        }

        $base = $existing ? $existing->only($addressKeys) : [];
        /** @var array{cep?: string|null, logradouro?: string|null, numero?: string|null, bairro?: string|null, cidade?: string|null, uf?: string|null} $merged */
        $merged = array_merge($base, array_intersect_key($data, array_flip($addressKeys)));

        $allEmpty = true;
        foreach ($merged as $v) {
            if (is_string($v) && trim($v) !== '') {
                $allEmpty = false;
                break;
            }
            if ($v !== null && ! is_string($v)) {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            $data['latitude'] = null;
            $data['longitude'] = null;

            return $data;
        }

        if (! NominatimGeocoder::hasSufficientAddress($merged)) {
            return $data;
        }

        $coords = NominatimGeocoder::geocode($merged);
        if ($coords === null) {
            return $data;
        }

        $data['latitude'] = $coords['latitude'];
        $data['longitude'] = $coords['longitude'];

        return $data;
    }
}
