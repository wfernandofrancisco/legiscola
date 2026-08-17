<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Noticia;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalNoticiaController extends Controller
{
    public function __construct(
        private PortalCatalogRepositoryInterface $catalog,
    ) {}

    public function index(Request $request): View
    {
        return view('portal.noticias.index', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'noticias' => $this->catalog->paginatePublishedNews((int) $request->integer('per_page') ?: 12),
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $noticia = $this->catalog->findPublishedNewsBySlug($slug);
        abort_if($noticia === null, 404);

        if ($noticia->tipo === Noticia::TIPO_RAPIDA && $noticia->fonte_url) {
            return redirect()->away($noticia->fonte_url);
        }

        return view('portal.noticias.show', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'noticia' => $noticia,
        ]);
    }
}
