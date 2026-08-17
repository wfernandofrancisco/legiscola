<?php

namespace App\Http\Requests\Aluno;

use App\Models\Student;
use App\Rules\CpfRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAlunoCadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Student::query()->where('user_id', $this->user()->id)->exists();
    }

    protected function prepareForValidation(): void
    {
        $digits = static fn (?string $v): string => preg_replace('/\D/', '', (string) $v);

        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'cpf' => $digits($this->input('cpf')),
            'cidade' => trim((string) $this->input('cidade', '')),
            'name' => trim((string) $this->input('name', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Student|null $student */
        $student = Student::query()->where('user_id', $this->user()->id)->first();
        $userId = $this->user()->id;
        $studentId = $student?->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                Rule::unique('students', 'email')->ignore($studentId),
            ],
            'cpf' => [
                'required',
                'string',
                'size:11',
                new CpfRule,
                Rule::unique('students', 'cpf')->ignore($studentId),
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'sexo' => ['required', 'in:masculino,feminino,outro,nao_informado'],
            'cidade' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome completo',
            'email' => 'e-mail',
            'cpf' => 'CPF',
            'birth_date' => 'data de nascimento',
            'sexo' => 'sexo',
            'cidade' => 'cidade',
            'password' => 'senha',
        ];
    }
}
