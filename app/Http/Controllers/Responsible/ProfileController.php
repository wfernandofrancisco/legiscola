<?php

namespace App\Http\Controllers\Responsible;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Perfil dentro de /professor — não reutiliza /admin para evitar navegação e UI de tenant_admin.
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('responsible.dashboard')],
            ['label' => 'Meu perfil'],
        ];

        return view('responsible.perfil.edit', compact('breadcrumbs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está registrado.',
        ]);

        $request->user()->fill([
            'name' => $request->name,
            'email' => strtolower(trim((string) $request->email)),
            'phone' => $request->phone,
        ])->save();

        return redirect()->route('responsible.perfil.edit')
            ->with('success', 'Seus dados foram atualizados com sucesso.');
    }

    public function changePassword(Request $request): RedirectResponse
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

        return redirect()->route('responsible.perfil.edit')
            ->with('success', 'Sua senha foi alterada com sucesso.');
    }
}
