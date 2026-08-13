<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\GlobalPrivacyTerm;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\View\View;

class PortalPrivacidadeController extends Controller
{
    public function show(): View
    {
        return view('portal.privacidade.show', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'term' => GlobalPrivacyTerm::currentPublished(),
        ]);
    }
}
