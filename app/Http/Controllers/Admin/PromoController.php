<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogLicense;
use App\Models\CatalogPromo;
use App\Services\CatalogPromoService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function __construct(private CatalogPromoService $promos) {}

    public function show(CatalogPromo $promo): View|RedirectResponse
    {
        $this->authorize('view', $promo);

        $promo->load([
            'catalogItem.lessons',
            'director' => fn ($q) => $q->withoutGlobalScopes([\App\Scopes\TenantScope::class])->select('id', 'name'),
        ]);

        $licenca = CatalogLicense::query()
            ->forTenant((int) TenantContext::getTenantId())
            ->where('catalog_item_id', $promo->catalog_item_id)
            ->usable()
            ->first();

        // Abrir o detalhe já conta como “vi” — não volta a aparecer todo dia.
        $this->promos->dismiss($promo, request()->user());

        return view('admin.promos.show', [
            'promo' => $promo,
            'item' => $promo->catalogItem,
            'licenca' => $licenca,
        ]);
    }

    public function dismiss(CatalogPromo $promo): RedirectResponse
    {
        $this->authorize('dismiss', $promo);

        $this->promos->dismiss($promo, request()->user());

        return back()->with('success', 'Aviso fechado. Ele só volta se a direção regional atualizar o conteúdo.');
    }
}
