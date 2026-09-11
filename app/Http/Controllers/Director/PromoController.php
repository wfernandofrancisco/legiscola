<?php

namespace App\Http\Controllers\Director;

use App\Enums\CatalogItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreCatalogPromoRequest;
use App\Http\Requests\Director\UpdateCatalogPromoRequest;
use App\Models\CatalogItem;
use App\Models\CatalogPromo;
use App\Models\Tenant;
use App\Services\CatalogPromoService;
use App\Support\DirectorContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function __construct(private CatalogPromoService $promos) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CatalogPromo::class);

        $promos = CatalogPromo::query()
            ->where('director_user_id', $request->user()->id)
            ->with(['catalogItem', 'tenants'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('director.promos.index', compact('promos'));
    }

    public function create(): View
    {
        $this->authorize('create', CatalogPromo::class);

        return view('director.promos.create', $this->formData());
    }

    public function store(StoreCatalogPromoRequest $request): RedirectResponse
    {
        $this->authorize('create', CatalogPromo::class);

        $promo = $this->promos->create($request->user(), $request->validated());

        return redirect()
            ->route('diretor.promos.index')
            ->with('success', "Aviso \"{$promo->titulo}\" publicado para as câmaras.");
    }

    public function edit(CatalogPromo $promo): View
    {
        $this->authorize('update', $promo);

        $promo->load(['catalogItem', 'tenants']);

        return view('director.promos.edit', array_merge($this->formData(), [
            'promo' => $promo,
        ]));
    }

    public function update(UpdateCatalogPromoRequest $request, CatalogPromo $promo): RedirectResponse
    {
        $this->authorize('update', $promo);

        $this->promos->update($promo, $request->validated());

        return redirect()
            ->route('diretor.promos.index')
            ->with('success', 'Aviso atualizado. Quem já tinha fechado volta a ver, porque o conteúdo mudou.');
    }

    public function destroy(CatalogPromo $promo): RedirectResponse
    {
        $this->authorize('delete', $promo);

        $promo->delete();

        return redirect()
            ->route('diretor.promos.index')
            ->with('success', 'Aviso removido.');
    }

    /**
     * @return array{itens: \Illuminate\Support\Collection, camaras: \Illuminate\Support\Collection}
     */
    private function formData(): array
    {
        return [
            'itens' => CatalogItem::query()
                ->where('owner_user_id', auth()->id())
                ->where('status', CatalogItemStatus::Publicado)
                ->orderBy('titulo')
                ->get(['id', 'titulo', 'tipo']),
            'camaras' => DirectorContext::tenants()
                ->where('status', Tenant::STATUS_ATIVO)
                ->where('cadastro_status', Tenant::CADASTRO_ATIVO)
                ->orderBy('estado')
                ->orderBy('name')
                ->get(['id', 'name', 'nome_fantasia', 'cidade', 'estado']),
        ];
    }
}
