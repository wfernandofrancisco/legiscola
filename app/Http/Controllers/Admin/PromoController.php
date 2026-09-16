<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCatalogPromoContactRequest;
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

    public function contact(StoreCatalogPromoContactRequest $request, CatalogPromo $promo): RedirectResponse
    {
        $this->authorize('contact', $promo);

        $this->promos->storeContact($promo, $request->user(), $request->validated());

        return back()->with('success', 'Mensagem enviada à direção regional. Eles veem o contato no painel de avisos.');
    }

    public function dismiss(CatalogPromo $promo): RedirectResponse
    {
        $this->authorize('dismiss', $promo);

        $this->promos->dismiss($promo, request()->user());

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Aviso fechado. Ele não aparece mais até a direção regional atualizar o conteúdo.');
    }
}
