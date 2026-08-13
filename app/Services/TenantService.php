<?php

namespace App\Services;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\TenantServiceInterface;
use App\Enums\TenantModulosPlano;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class TenantService implements TenantServiceInterface
{
    public function __construct(private TenantRepositoryInterface $tenantRepository) {}

    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->tenantRepository->paginateWithSearch($perPage, $search);
    }

    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        return $this->tenantRepository->paginateWithSort($perPage, $search, $sortBy, $sortDir);
    }

    public function getTenant(int $id): Tenant
    {
        return $this->tenantRepository->findWithRelations($id)
            ?? throw (new ModelNotFoundException)->setModel(Tenant::class, [$id]);
    }

    public function createTenant(array $data): Tenant
    {
        $name = $data['name'] ?? $data['nome_fantasia'] ?? $data['razao_social'] ?? 'Cliente';

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($name);
        }

        $originalSlug = $data['slug'];
        $counter = 1;
        while ($this->tenantRepository->existsBySlug($data['slug'])) {
            $data['slug'] = $originalSlug.'-'.$counter;
            $counter++;
        }

        $cadastro = $data['cadastro_status'] ?? Tenant::CADASTRO_PENDENTE;

        $payload = [
            'name' => $name,
            'slug' => $data['slug'],
            'domain' => $data['domain'] ?? $this->extractDomainFromWebsite($data['website'] ?? null),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? Tenant::STATUS_ATIVO,
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
            'subscription_expires_at' => $data['subscription_expires_at'] ?? null,
            'razao_social' => $data['razao_social'] ?? null,
            'nome_fantasia' => $data['nome_fantasia'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'contact_email' => $data['contact_email'] ?? $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'codigo_ibge_municipio' => $data['codigo_ibge_municipio'] ?? null,
            'codigo_municipio_estban' => $data['codigo_municipio_estban'] ?? null,
            'codigo_municipio_caged' => $data['codigo_municipio_caged'] ?? null,
            'codigo_importacao_exportacao' => $data['codigo_importacao_exportacao'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'cadastro_status' => $cadastro,
            'modulos_plano' => (TenantModulosPlano::tryFrom((string) ($data['modulos_plano'] ?? '')) ?? TenantModulosPlano::ADMIN)->value,
        ];

        $tenant = $this->tenantRepository->create($payload);

        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($tenant)
            ->log('Novo tenant criado');

        return $tenant;
    }

    public function updateTenant(int $id, array $data): Tenant
    {
        $tenant = Tenant::query()->findOrFail($id);

        if (isset($data['name']) && $data['name'] !== $tenant->name && empty($data['slug'])) {
            $newSlug = Str::slug($data['name']);
            if ($newSlug !== $tenant->slug) {
                $originalSlug = $newSlug;
                $counter = 1;
                while ($this->tenantRepository->model->where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
                    $newSlug = $originalSlug.'-'.$counter;
                    $counter++;
                }
                $data['slug'] = $newSlug;
            }
        }

        $allowed = [
            'name', 'slug', 'domain', 'description', 'status',
            'trial_ends_at', 'subscription_expires_at',
            'razao_social', 'nome_fantasia', 'cnpj', 'contact_email', 'email',
            'phone', 'website', 'cep', 'logradouro', 'numero', 'complemento',
            'bairro', 'cidade', 'estado', 'latitude', 'longitude', 'codigo_ibge_municipio', 'codigo_municipio_estban', 'codigo_municipio_caged', 'codigo_importacao_exportacao', 'observacoes', 'cadastro_status', 'modulos_plano',
        ];

        $updates = array_intersect_key($data, array_flip($allowed));

        if (isset($updates['email']) && ! isset($updates['contact_email'])) {
            $updates['contact_email'] = $updates['email'];
            unset($updates['email']);
        }

        if (array_key_exists('website', $updates) && ! array_key_exists('domain', $updates)) {
            $updates['domain'] = $this->extractDomainFromWebsite($updates['website']);
        }

        $this->tenantRepository->update($id, $updates);

        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($tenant)
            ->log("Tenant \"{$tenant->name}\" atualizado");

        return $this->tenantRepository->findWithRelations($id)
            ?? throw (new ModelNotFoundException)->setModel(Tenant::class, [$id]);
    }

    public function deleteTenant(int $id): bool
    {
        $tenant = $this->tenantRepository->find($id);
        $result = $this->tenantRepository->delete($id);

        if ($result) {
            activity('central')
                ->causedBy(auth()->user())
                ->log("Tenant \"{$tenant->name}\" removido");
        }

        return $result;
    }

    public function linkUserToTenant(int $tenantId, int $userId, ?string $cargo = null, bool $isPrimary = true): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $user = User::findOrFail($userId);

        $shouldSetAdmin = $isPrimary || ! $user->tenant_id;

        $updates = ['tenant_id' => $tenant->id];

        if ($shouldSetAdmin) {
            $updates['user_type'] = User::TYPE_TENANT_ADMIN;
        } elseif (! in_array($user->user_type, [
            User::TYPE_TENANT_ADMIN,
            User::TYPE_TENANT_MANAGER,
            User::TYPE_TENANT_USER,
        ], true)) {
            $updates['user_type'] = User::TYPE_TENANT_USER;
        }

        $user->update($updates);
    }

    public function unlinkUserFromTenant(int $tenantId, int $userId): void
    {
        $user = User::findOrFail($userId);

        if ((int) $user->tenant_id !== $tenantId) {
            return;
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        $user->update(['tenant_id' => null]);
    }

    private function extractDomainFromWebsite(?string $website): ?string
    {
        if (! $website) {
            return null;
        }

        $normalizedWebsite = preg_match('#^https?://#i', $website) ? $website : 'https://'.$website;
        $host = parse_url($normalizedWebsite, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }
}
