<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StorePortalContactRequest;
use App\Mail\PortalContactSubmittedMail;
use App\Models\PortalContactMessage;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PortalContatoController extends Controller
{
    public function index(): View
    {
        return view('portal.contato.index', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
        ]);
    }

    public function store(StorePortalContactRequest $request): RedirectResponse
    {
        $tenant = Tenant::query()->findOrFail((int) TenantContext::getTenantId());
        $validated = $request->validated();

        PortalContactMessage::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
        ]);

        $to = trim((string) ($tenant->contact_email ?? ''));
        if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Mail::to($to)->queue(new PortalContactSubmittedMail($tenant, $validated));
        }

        return redirect()
            ->route('portal.contato')
            ->with('success', 'Mensagem registrada com sucesso. Retornaremos em breve.');
    }
}
