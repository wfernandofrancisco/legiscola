<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalProfessorController extends Controller
{
    public function __construct(
        private PortalCatalogRepositoryInterface $catalog,
    ) {}

    public function index(Request $request): View
    {
        return view('portal.professores.index', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'credenciamentos' => $this->catalog->paginateActiveCredenciamentos(
                (int) $request->integer('cred_per_page') ?: 8,
                'cred_page'
            ),
            'professores' => $this->catalog->paginateTeachers(
                (int) $request->integer('prof_per_page') ?: 12,
                'prof_page'
            ),
        ]);
    }
}
