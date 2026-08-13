<?php

namespace App\Http\Requests\Auth;

use App\Enums\Escolaridade;
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

        $email = strtolower(trim((string) $this->input('email', '')));

        $cep = $digits($this->input('cep'));
        $telefone = $digits($this->input('telefone'));
        $celular = $digits($this->input('celular'));
        $uf = strtoupper(trim((string) $this->input('uf', '')));

        $this->merge([
            'email' => $email,
            'cpf' => $digits($this->input('cpf')),
            'cep' => $cep !== '' ? $cep : null,
            'telefone' => $telefone !== '' ? $telefone : null,
            'celular' => $celular !== '' ? $celular : null,
            'uf' => $uf !== '' ? $uf : null,
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
            'telefone' => ['nullable', 'string', 'max:11'],
            'celular' => ['nullable', 'string', 'max:11'],
            'cep' => ['nullable', 'string', 'max:8'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'profissao' => ['nullable', 'string', 'max:255'],
            'escolaridade' => ['required', Rule::enum(Escolaridade::class)],
            'photo' => ['nullable', 'image', 'max:2048'],
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
            'telefone' => 'telefone',
            'celular' => 'celular',
            'cep' => 'CEP',
            'logradouro' => 'logradouro',
            'numero' => 'número',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'uf' => 'UF',
            'profissao' => 'profissão',
            'escolaridade' => 'escolaridade',
            'photo' => 'foto',
            'accept_global_privacy' => 'aceite da política de privacidade',
        ];
    }
}
