<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Aluno\UpdateAlunoCadastroRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View as IlluminateView;

class CadastroController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function edit(): IlluminateView|RedirectResponse
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        if (! $student instanceof Student) {
            return redirect()
                ->route('app.dashboard')
                ->with('error', 'Cadastro de aluno não encontrado para esta conta.');
        }

        return view('aluno.cadastro', compact('student'));
    }

    public function update(UpdateAlunoCadastroRequest $request): RedirectResponse
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        if (! $student instanceof Student) {
            return redirect()
                ->route('app.dashboard')
                ->with('error', 'Cadastro de aluno não encontrado para esta conta.');
        }

        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data);

        $user = $request->user();
        $newEmail = (string) $data['email'];
        if ($user->email !== $newEmail) {
            $user->forceFill([
                'email' => $newEmail,
                'email_verified_at' => null,
            ])->save();
        }

        return redirect()->route('app.cadastro.edit')
            ->with('success', 'Dados atualizados com sucesso.');
    }
}
