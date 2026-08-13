<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyPortalContactMessageRequest;
use App\Mail\PortalContactReplyMail;
use App\Models\PortalContactMessage;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PortalContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = PortalContactMessage::query()
            ->latest()
            ->paginate(20);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Contatos do portal'],
        ];

        return view('admin.portal-contacts.index', compact('messages', 'breadcrumbs'));
    }

    public function show(PortalContactMessage $contato): View
    {
        if ($contato->read_at === null) {
            $contato->forceFill(['read_at' => now()])->save();
        }

        $contato->load('repliedBy:id,name');
        $tenant = Tenant::query()->findOrFail((int) $contato->tenant_id);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Contatos do portal', 'href' => route('admin.contatos-portal.index')],
            ['label' => 'Mensagem #'.$contato->id],
        ];

        return view('admin.portal-contacts.show', compact('contato', 'tenant', 'breadcrumbs'));
    }

    public function reply(ReplyPortalContactMessageRequest $request, PortalContactMessage $contato): RedirectResponse
    {
        $body = trim((string) $request->validated()['reply_body']);
        $tenant = Tenant::query()->findOrFail((int) $contato->tenant_id);

        Mail::to($contato->email)->queue(new PortalContactReplyMail($tenant, $contato, $body));

        $contato->forceFill([
            'reply_body' => $body,
            'replied_at' => now(),
            'replied_by_user_id' => auth()->id(),
        ])->save();

        return back()->with('success', 'Resposta enviada por e-mail para '.$contato->email.'.');
    }
}
