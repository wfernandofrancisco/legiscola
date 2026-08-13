<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\UpdateAppPasswordRequest;
use App\Http\Requests\App\UpdateAppProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('app.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateAppProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('app.profile.edit')
            ->with('success', 'Perfil atualizado.');
    }

    public function updatePassword(UpdateAppPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('app.profile.edit')
            ->with('success', 'Senha alterada com sucesso.');
    }
}
