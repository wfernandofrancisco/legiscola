<?php

namespace App\Services;

use App\Models\CatalogItem;
use App\Models\CatalogPromo;
use App\Models\CatalogPromoDismissal;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Support\DirectorContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CatalogPromoService
{
    public function create(User $director, array $data): CatalogPromo
    {
        $item = $this->assertOwnItem($director, (int) $data['catalog_item_id']);
        $alcance = $data['alcance'] ?? CatalogPromo::ALCANCE_GERAL;
        $tenantIds = $this->resolveTenantIds($alcance, $data['tenant_ids'] ?? []);

        $promo = CatalogPromo::create([
            'director_user_id' => $director->id,
            'catalog_item_id' => $item->id,
            'titulo' => $data['titulo'],
            'mensagem' => $data['mensagem'] ?? null,
            'preco_de' => $data['preco_de'] ?? null,
            'preco_por' => $data['preco_por'] ?? null,
            'desconto_percentual' => $data['desconto_percentual'] ?? null,
            'alcance' => $alcance,
            'inicia_em' => $data['inicia_em'] ?? null,
            'termina_em' => $data['termina_em'] ?? null,
            'ativo' => (bool) ($data['ativo'] ?? true),
        ]);

        if ($alcance === CatalogPromo::ALCANCE_ESPECIFICO) {
            $promo->tenants()->sync($tenantIds);
        }

        return $promo;
    }

    public function update(CatalogPromo $promo, array $data): CatalogPromo
    {
        $item = $this->assertOwnItem($promo->director, (int) ($data['catalog_item_id'] ?? $promo->catalog_item_id));
        $alcance = $data['alcance'] ?? $promo->alcance;
        $tenantIds = $this->resolveTenantIds($alcance, $data['tenant_ids'] ?? []);

        $promo->fill([
            'catalog_item_id' => $item->id,
            'titulo' => $data['titulo'],
            'mensagem' => $data['mensagem'] ?? null,
            'preco_de' => $data['preco_de'] ?? null,
            'preco_por' => $data['preco_por'] ?? null,
            'desconto_percentual' => $data['desconto_percentual'] ?? null,
            'alcance' => $alcance,
            'inicia_em' => $data['inicia_em'] ?? null,
            'termina_em' => $data['termina_em'] ?? null,
            'ativo' => array_key_exists('ativo', $data) ? (bool) $data['ativo'] : $promo->ativo,
        ]);
        $promo->save();

        if ($alcance === CatalogPromo::ALCANCE_ESPECIFICO) {
            $promo->tenants()->sync($tenantIds);
        } else {
            $promo->tenants()->detach();
        }

        // Touch explícito: qualquer edição deve poder reaparecer no dashboard do admin.
        $promo->touch();

        return $promo->fresh(['catalogItem', 'tenants']);
    }

    public function dismiss(CatalogPromo $promo, User $user): void
    {
        CatalogPromoDismissal::query()->updateOrCreate(
            [
                'catalog_promo_id' => $promo->id,
                'user_id' => $user->id,
            ],
            [
                'promo_updated_at' => $promo->updated_at,
                'dismissed_at' => now(),
            ]
        );
    }

    /**
     * Avisos que o admin deve ver no dashboard agora.
     *
     * @return Collection<int, CatalogPromo>
     */
    public function forAdminDashboard(User $admin): Collection
    {
        if (! $admin->tenant_id) {
            return collect();
        }

        return CatalogPromo::query()
            ->currentlyActive()
            ->forTenant((int) $admin->tenant_id)
            ->notDismissedBy((int) $admin->id)
            ->with([
                'catalogItem.lessons',
                'director' => fn ($q) => $q->withoutGlobalScopes([TenantScope::class])->select('id', 'name'),
            ])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
    }

    private function assertOwnItem(User $director, int $itemId): CatalogItem
    {
        $item = CatalogItem::query()
            ->where('owner_user_id', $director->id)
            ->whereKey($itemId)
            ->first();

        if (! $item) {
            throw ValidationException::withMessages([
                'catalog_item_id' => 'Selecione um item do seu catálogo.',
            ]);
        }

        return $item;
    }

    /**
     * @param  list<int|string>  $requested
     * @return list<int>
     */
    private function resolveTenantIds(string $alcance, array $requested): array
    {
        if ($alcance !== CatalogPromo::ALCANCE_ESPECIFICO) {
            return [];
        }

        // Abrangência pelas UFs do diretor autenticado (ou do argumento, se o contexto ainda não estiver primed).
        $allowedQuery = DirectorContext::hasScope()
            ? DirectorContext::tenants()
            : Tenant::query()->whereIn('estado', Auth::user()?->directorUfCodes() ?? []);

        $allowed = $allowedQuery
            ->where('status', Tenant::STATUS_ATIVO)
            ->where('cadastro_status', Tenant::CADASTRO_ATIVO)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $ids = collect($requested)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => in_array($id, $allowed, true))
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'tenant_ids' => 'Escolha ao menos uma câmara ativa da sua região.',
            ]);
        }

        return $ids;
    }
}
