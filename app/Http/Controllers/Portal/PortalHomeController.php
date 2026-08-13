<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Services\Portal\PortalHomeServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\View\View;

class PortalHomeController extends Controller
{
    public function __construct(
        private PortalHomeServiceInterface $homeService,
    ) {}

    public function index(): View
    {
        if (! TenantContext::isSet()) {
            return view('public.index');
        }

        $tenant = Tenant::query()->findOrFail((int) TenantContext::getTenantId());

        return view('portal.home', array_merge(
            ['tenant' => $tenant],
            $this->homeService->homePayload()
        ));
    }
}
