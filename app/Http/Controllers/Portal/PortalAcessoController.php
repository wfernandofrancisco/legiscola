<?php

namespace App\Http\Controllers\Portal;

use App\Enums\Escolaridade;
use App\Http\Controllers\Controller;
use App\Models\GlobalPrivacyTerm;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use App\Support\TenantContext;
use Illuminate\View\View;

class PortalAcessoController extends Controller
{
    public function login(): View
    {
        return view('portal.acesso.login', $this->authPayload());
    }

    public function register(): View
    {
        return view('portal.acesso.register', array_merge($this->authPayload(), [
            'escolaridadeOptions' => Escolaridade::options(),
            'globalPrivacyTerm' => GlobalPrivacyTerm::currentPublished(),
        ]));
    }

    public function forgot(): View
    {
        return view('portal.acesso.forgot-password', $this->authPayload());
    }

    public function docenteLogin(): View
    {
        return view('portal.acesso.docente-login', $this->authPayload());
    }

    public function docenteForgot(): View
    {
        return view('portal.acesso.docente-forgot-password', $this->authPayload());
    }

    /**
     * @return array{tenant: Tenant, settings: TenantAdminSetting|null}
     */
    private function authPayload(): array
    {
        $tenantId = TenantContext::getTenantId();

        return [
            'tenant' => Tenant::query()->findOrFail((int) $tenantId),
            'settings' => TenantAdminSetting::query()->where('tenant_id', $tenantId)->first(),
        ];
    }
}
