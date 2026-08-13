<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Support\ProfessorContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        ProfessorContext::requireDocentePainel();
        $teacher = ProfessorContext::teacher();
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Meu perfil'],
        ];

        return view('professor.perfil.edit', compact('breadcrumbs', 'teacher'));
    }

    public function update(Request $request): RedirectResponse
    {
        ProfessorContext::requireDocentePainel();
        $teacher = ProfessorContext::teacher();
        $user = $request->user();

        if (! $teacher) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
                'phone' => ['nullable', 'string', 'max:20'],
            ], [
                'name.required' => 'O nome é obrigatório.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'O e-mail deve ser válido.',
                'email.unique' => 'Este e-mail já está registrado.',
            ]);

            $user->fill([
                'name' => $validated['name'],
                'email' => strtolower(trim((string) $validated['email'])),
                'phone' => $validated['phone'] ?? null,
            ])->save();

            return redirect()->route('professor.perfil.edit')
                ->with('success', 'Seus dados foram atualizados com sucesso.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'celular' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'specialities' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $phone = preg_replace('/\D/', '', (string) ($data['celular'] ?? '')) ?: null;

        if ($request->hasFile('photo')) {
            if ($teacher->photo_path) {
                Storage::disk('public')->delete($teacher->photo_path);
            }
            $path = $request->file('photo')->store('teachers', 'public');
            $teacher->photo_path = $path;
            $user->avatar = $path;
        }

        $teacher->update([
            'full_name' => $data['full_name'],
            'email' => strtolower(trim($data['email'])),
            'phone' => $phone,
            'bio' => $data['bio'] ?? null,
            'specialities' => $data['specialities'] ?? null,
        ]);

        $user->update([
            'name' => $data['full_name'],
            'email' => strtolower(trim($data['email'])),
            'phone' => $phone,
        ]);

        return redirect()->route('professor.perfil.edit')->with('success', 'Perfil atualizado.');
    }
}
