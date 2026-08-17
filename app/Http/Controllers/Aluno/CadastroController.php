<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Aluno\UpdateAlunoCadastroRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
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
        $password = (string) ($data['password'] ?? '');
        unset($data['password'], $data['password_confirmation'], $data['name']);

        $student->update($data);

        $user = $request->user();
        $userUpdate = [
            'name' => $request->validated('name'),
            'cpf' => $data['cpf'],
        ];

        $newEmail = (string) $data['email'];
        if ($user->email !== $newEmail) {
            $userUpdate['email'] = $newEmail;
            $userUpdate['email_verified_at'] = null;
        }

        if ($password !== '') {
            $userUpdate['password'] = Hash::make($password);
        }

        $user->forceFill($userUpdate)->save();

        return redirect()->route('app.cadastro.edit')
            ->with('success', 'Dados atualizados com sucesso.');
    }
}
