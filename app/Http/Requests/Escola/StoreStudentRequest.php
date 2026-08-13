<?php

namespace App\Http\Requests\Escola;

use App\Enums\Escolaridade;
use App\Rules\CpfRule;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $studentId = $student?->id;
        $currentUserId = $student?->user_id;

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
            'enrollment_number' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:masculino,feminino,outro,nao_informado'],
            'cpf' => ['required', 'string', 'max:14', new CpfRule, Rule::unique('students', 'cpf')->ignore($studentId)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'cep' => ['nullable', 'string', 'max:9'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'profissao' => ['nullable', 'string', 'max:255'],
            'escolaridade' => ['nullable', Rule::in(array_keys(Escolaridade::options()))],
            'status' => ['required', 'in:ativo,inativo'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
