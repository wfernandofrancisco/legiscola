<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\GlobalPrivacyTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalPrivacyTermController extends Controller
{
    public function edit(): View
    {
        $term = GlobalPrivacyTerm::document();

        return view('central.global-privacy-terms.edit', [
            'term' => $term,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $term = GlobalPrivacyTerm::document();
        $action = (string) $request->input('action', 'draft');

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'body_html' => ['nullable', 'string'],
        ];

        if ($action === 'publish') {
            $rules['body_html'] = ['required', 'string', 'min:100'];
        }

        $validated = $request->validate($rules, [], [
            'title' => 'título',
            'body_html' => 'texto do termo',
        ]);

        $term->title = $validated['title'];
        $term->body_html = $validated['body_html'] ?? '';

        if ($action === 'publish') {
            $term->version = (int) $term->version + 1;
            $term->published_at = now();
        }

        $term->save();

        return redirect()
            ->route('central.global-privacy-term.edit')
            ->with('success', $action === 'publish'
                ? 'Nova versão publicada. Os utilizadores terão de aceitar novamente.'
                : 'Rascunho guardado.');
    }
}
