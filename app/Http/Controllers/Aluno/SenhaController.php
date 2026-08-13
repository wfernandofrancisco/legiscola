<?php

namespace App\Http\Controllers\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\UpdateAppPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SenhaController extends Controller
{
    public function edit(): View
    {
        return view('aluno.senha');
    }

    public function update(UpdateAppPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('app.senha.edit')
            ->with('success', 'Senha alterada com sucesso.');
    }
}
