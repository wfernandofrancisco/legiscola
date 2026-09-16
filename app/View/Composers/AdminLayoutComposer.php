<?php

namespace App\View\Composers;

use App\Models\CatalogLicense;
use App\Services\CatalogPromoService;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function __construct(private CatalogPromoService $promos) {}

    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isTenantAdmin()) {
            $view->with([
                'avisosRegionais' => collect(),
                'licencasPendentes' => collect(),
            ]);

            return;
        }

        $tenantId = (int) ($user->tenant_id ?: TenantContext::getTenantId());

        $view->with([
            'avisosRegionais' => $this->promos->forAdminDashboard($user),
            'licencasPendentes' => $tenantId > 0
                ? CatalogLicense::pendingAgendaForTenant($tenantId)
                : collect(),
        ]);
    }
}
