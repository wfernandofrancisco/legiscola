<?php

namespace App\Http\Requests\Auth;

use App\Models\GlobalPrivacyTerm;
use App\Rules\CpfRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterPortalAlunoRequest extends FormRequest
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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email', 'unique:students,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'birth_date' => ['required', 'date', 'before:today'],
            'sexo' => ['required', 'in:masculino,feminino,outro,nao_informado'],
            'cpf' => ['required', 'string', 'size:11', new CpfRule, Rule::unique('students', 'cpf')],
            'cidade' => ['required', 'string', 'max:255'],
        ];

        if (GlobalPrivacyTerm::currentPublished() !== null) {
            $rules['accept_global_privacy'] = ['accepted'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'accept_global_privacy.accepted' => 'É necessário ler e aceitar a política de privacidade para concluir o cadastro.',
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
            'password' => 'senha',
            'birth_date' => 'data de nascimento',
            'sexo' => 'sexo',
            'cpf' => 'CPF',
            'cidade' => 'cidade',
            'accept_global_privacy' => 'aceite da política de privacidade',
        ];
    }
}
