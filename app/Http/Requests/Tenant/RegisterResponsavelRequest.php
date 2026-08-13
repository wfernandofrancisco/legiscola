<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterResponsavelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')) ?? '',
            'cnpj_empresa' => preg_replace('/\D/', '', (string) $this->input('cnpj_empresa')) ?? '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'cpf' => ['required', 'string', 'size:11', 'unique:users,cpf'],
            'phone' => ['required', 'string', 'max:20'],
            'cnpj_empresa' => ['required', 'string', 'size:14'],
            'razao_social_informada' => ['nullable', 'string', 'max:255'],
            'mensagem' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
