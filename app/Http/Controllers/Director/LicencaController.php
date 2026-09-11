<?php

namespace App\Http\Controllers\Director;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogLicenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreCatalogLicenseRequest;
use App\Http\Requests\Director\UpdateCatalogLicenseRequest;
use App\Mail\CatalogLicenseReleasedMail;
use App\Models\CatalogItem;
use App\Models\CatalogLicense;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CatalogLicenseService;
use App\Support\DirectorContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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

        $avisados = $this->notificarCamara($licenca);

        $mensagem = "\"{$licenca->catalogItem->titulo}\" liberado para {$licenca->tenant->display_name}.";
        $mensagem .= $avisados > 0
            ? " Avisamos {$avisados} responsável(is) da câmara por e-mail."
            : ' A câmara não tem administrador com e-mail para avisar.';

        return redirect()
            ->route('diretor.licencas.index')
            ->with('success', $mensagem);
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

        $this->licenses->update(
            $licenca,
            $request->validated(),
            $request->file('nota_fiscal_arquivo'),
            $request->boolean('remover_nota_fiscal_arquivo')
        );

        return redirect()
            ->route('diretor.licencas.index')
            ->with('success', 'Licença atualizada.');
    }

    /**
     * A nota fiscal fica em disco privado; só o diretor dono da licença baixa.
     */
    public function notaFiscal(CatalogLicense $licenca): StreamedResponse
    {
        $this->authorize('update', $licenca);

        $path = $licenca->nota_fiscal_arquivo_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
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
     * Avisa os administradores da câmara que há conteúdo novo para colocar em agenda.
     *
     * Licença que nasce suspensa não gera aviso: não há o que a câmara fazer com ela ainda.
     * Falha de e-mail não derruba a liberação — a licença já vale pelo painel.
     */
    private function notificarCamara(CatalogLicense $licenca): int
    {
        if (! $licenca->status->isUsable()) {
            return 0;
        }

        $admins = User::query()
            ->where('tenant_id', $licenca->tenant_id)
            ->where('user_type', User::TYPE_TENANT_ADMIN)
            ->where('status', User::STATUS_ATIVO)
            ->whereNotNull('email')
            ->get(['id', 'name', 'email']);

        $enviados = 0;

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new CatalogLicenseReleasedMail($licenca, $admin->name));
                $enviados++;
            } catch (Throwable $e) {
                Log::warning('Falha ao avisar câmara sobre licença liberada.', [
                    'catalog_license_id' => $licenca->id,
                    'user_id' => $admin->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $enviados;
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
