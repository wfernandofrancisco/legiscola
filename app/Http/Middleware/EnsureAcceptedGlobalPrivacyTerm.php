<?php

namespace App\Http\Middleware;

use App\Models\GlobalPrivacyTerm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcceptedGlobalPrivacyTerm
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        if ($request->routeIs('privacy-term.show', 'privacy-term.accept')) {
            return $next($request);
        }

        $term = GlobalPrivacyTerm::currentPublished();
        if ($term === null) {
            return $next($request);
        }

        $accepted = (int) ($user->accepted_global_privacy_term_version ?? 0);
        if ($accepted >= (int) $term->version) {
            return $next($request);
        }

        return redirect()->route('privacy-term.show');
    }
}
