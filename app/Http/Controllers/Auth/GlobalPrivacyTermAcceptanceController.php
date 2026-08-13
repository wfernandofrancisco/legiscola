<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GlobalPrivacyTerm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalPrivacyTermAcceptanceController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $term = GlobalPrivacyTerm::currentPublished();
        if ($term === null) {
            return redirect()->intended(route($request->user()->tenantHomeRouteName()));
        }

        /** @var User $user */
        $user = $request->user();
        if ((int) ($user->accepted_global_privacy_term_version ?? 0) >= (int) $term->version) {
            return redirect()->intended(route($user->tenantHomeRouteName()));
        }

        return view('auth.privacy-term-accept', [
            'term' => $term,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $term = GlobalPrivacyTerm::currentPublished();
        if ($term === null) {
            return redirect()->intended(route($request->user()->tenantHomeRouteName()));
        }

        $request->validate([
            'accept' => ['accepted'],
        ], [
            'accept.accepted' => 'É necessário marcar que leu e concorda com o termo para continuar.',
        ]);

        /** @var User $user */
        $user = $request->user();
        if ((int) ($user->accepted_global_privacy_term_version ?? 0) >= (int) $term->version) {
            return redirect()->intended(route($user->tenantHomeRouteName()));
        }

        $user->forceFill([
            'accepted_global_privacy_term_version' => $term->version,
            'accepted_global_privacy_term_at' => now(),
        ])->save();

        return redirect()->intended(route($user->tenantHomeRouteName()));
    }
}
