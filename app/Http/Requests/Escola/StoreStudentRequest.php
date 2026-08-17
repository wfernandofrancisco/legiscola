<?php

namespace App\Http\Requests\Escola;

use App\Rules\CpfRule;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $digits = static fn (?string $v): string => preg_replace('/\D/', '', (string) $v);

        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'cpf' => $digits($this->input('cpf')),
            'cidade' => trim((string) $this->input('cidade', '')),
        ]);
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $studentId = $student?->id;
        $currentUserId = $student?->user_id;
        $isUpdate = $student !== null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($studentId),
                function (string $attribute, mixed $value, \Closure $fail) use ($currentUserId, $student): void {
                    $existingUser = User::query()->where('email', $value)->first();
                    if (! $student) {
                        return;
                    }
                    if ($existingUser && (int) $existingUser->id !== (int) $currentUserId) {
                        $fail('Este e-mail já está em uso por outro usuário.');
                    }
                },
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'sexo' => ['required', 'in:masculino,feminino,outro,nao_informado'],
            'cpf' => ['required', 'string', 'size:11', new CpfRule, Rule::unique('students', 'cpf')->ignore($studentId)],
            'cidade' => ['required', 'string', 'max:255'],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
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
            'birth_date' => 'data de nascimento',
            'sexo' => 'sexo',
            'cpf' => 'CPF',
            'cidade' => 'cidade',
            'password' => 'senha',
        ];
    }
}
