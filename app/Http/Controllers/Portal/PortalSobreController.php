<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PortalSobreController extends Controller
{
    public function __construct(
        private PortalCatalogRepositoryInterface $catalog,
    ) {}

    public function show(): View|RedirectResponse
    {
        $sobre = $this->catalog->firstSobreEscola();

        if ($sobre === null) {
            return redirect()->route('home')->with('warning', 'Conteúdo institucional em elaboração.');
        }

        return view('portal.sobre.show', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'sobreEscola' => $sobre,
        ]);
    }
}
