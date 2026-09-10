<?php

namespace App\Http\Controllers\Director;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogLicenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreCatalogLicenseRequest;
use App\Http\Requests\Director\UpdateCatalogLicenseRequest;
use App\Models\CatalogItem;
use App\Models\CatalogLicense;
use App\Models\Tenant;
use App\Services\CatalogLicenseService;
use App\Support\DirectorContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicencaController extends Controller
{
    public function __construct(private CatalogLicenseService $licenses) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CatalogLicense::class);

        $licencas = $this->baseQuery($request)
            ->with(['catalogItem', 'tenant'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('tenant'), fn ($q) => $q->where('tenant_id', $request->integer('tenant')))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('director.licencas.index', [
            'licencas' => $licencas,
            'statuses' => CatalogLicenseStatus::options(),
            'camaras' => $this->camaras(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CatalogLicense::class);

        return view('director.licencas.create', [
            'itens' => $this->itensPublicados(),
            'camaras' => $this->camaras(),
            'itemSelecionado' => $request->integer('item') ?: null,
        ]);
    }

    public function store(StoreCatalogLicenseRequest $request): RedirectResponse
    {
        $this->authorize('create', CatalogLicense::class);

        $licenca = $this->licenses->create(
            $request->user(),
            $request->catalogItem(),
            Tenant::findOrFail($request->integer('tenant_id')),
            $request->validated()
        );

        return redirect()
            ->route('diretor.licencas.index')
            ->with('success', "\"{$licenca->catalogItem->titulo}\" liberado para {$licenca->tenant->display_name}.");
    }

    public function edit(CatalogLicense $licenca): View
    {
        $this->authorize('update', $licenca);

        $licenca->load(['catalogItem', 'tenant']);

        return view('director.licencas.edit', ['licenca' => $licenca]);
    }

    public function update(UpdateCatalogLicenseRequest $request, CatalogLicense $licenca): RedirectResponse
    {
        $this->authorize('update', $licenca);

        $this->licenses->update($licenca, $request->validated());

        return redirect()
            ->route('diretor.licencas.index')
            ->with('success', 'Licença atualizada.');
    }

    public function destroy(CatalogLicense $licenca): RedirectResponse
    {
        $this->authorize('delete', $licenca);

        $licenca->delete();

        return redirect()
            ->route('diretor.licencas.index')
            ->with('success', 'Licença removida.');
    }

    /**
     * Licenças do diretor autenticado, sempre restritas à abrangência atual.
     */
    private function baseQuery(Request $request)
    {
        return CatalogLicense::query()
            ->where('director_user_id', $request->user()->id)
            ->whereIn('tenant_id', DirectorContext::tenantIds());
    }

    private function itensPublicados()
    {
        return CatalogItem::query()
            ->where('owner_user_id', auth()->id())
            ->where('status', CatalogItemStatus::Publicado)
            ->orderBy('titulo')
            ->get();
    }

    private function camaras()
    {
        return DirectorContext::tenants()
            ->select(['id', 'name', 'nome_fantasia', 'razao_social', 'cidade', 'estado'])
            ->orderBy('estado')
            ->orderBy('name')
            ->get();
    }
}
