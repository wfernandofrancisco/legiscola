<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\TenantServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTenantRequest;
use App\Http\Requests\Api\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(private TenantServiceInterface $tenantService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);
        $perPage = (int) $request->get('per_page', 15);

        $query = Tenant::query()
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q) => $q->where(function ($q2) use ($filters) {
                $s = $filters['search'];
                $q2->where('name', 'like', "%{$s}%")
                    ->orWhere('razao_social', 'like', "%{$s}%")
                    ->orWhere('cnpj', 'like', "%{$s}%");
            }))
            ->latest();

        $tenants = $query->paginate($perPage);

        activity('tenants')
            ->causedBy($request->user())
            ->log('Listagem de clientes (API)');

        return response()->json($tenants);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['invite_name_1'], $data['invite_email_1'], $data['invite_cargo_1']);

        $tenant = $this->tenantService->createTenant($data);

        return response()->json([
            'message' => 'Cliente criado com sucesso.',
            'tenant' => $tenant,
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenant = Tenant::withTrashed()->with(['users'])->findOrFail($id);

        activity('tenants')
            ->causedBy($request->user())
            ->performedOn($tenant)
            ->log('Cliente visualizado (API)');

        return response()->json($tenant);
    }

    public function update(UpdateTenantRequest $request, int $id): JsonResponse
    {
        $tenant = $this->tenantService->updateTenant($id, $request->validated());

        return response()->json([
            'message' => 'Cliente atualizado com sucesso.',
            'tenant' => $tenant,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->tenantService->deleteTenant($id);

        return response()->json(['message' => 'Cliente removido com sucesso.']);
    }

    public function linkUser(Request $request, int $tenantId): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'cargo' => ['nullable', 'string', 'max:100'],
        ]);

        $this->tenantService->linkUserToTenant($tenantId, (int) $request->user_id, $request->cargo);

        return response()->json(['message' => 'Usuário vinculado ao cliente com sucesso.']);
    }

    public function unlinkUser(int $tenantId, int $userId): JsonResponse
    {
        $this->tenantService->unlinkUserFromTenant($tenantId, $userId);

        return response()->json(['message' => 'Usuário desvinculado do cliente.']);
    }
}
