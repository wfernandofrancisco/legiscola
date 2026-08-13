<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Meu perfil', 'href' => route('professor.perfil.edit')],
            ['label' => 'Trocar senha'],
        ];

        return view('professor.senha.edit', compact('breadcrumbs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'A senha atual é obrigatória.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.confirmed' => 'As senhas não correspondem.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('professor.senha.edit')->with('success', 'Senha alterada com sucesso.');
    }
}
