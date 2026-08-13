<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'  => ['sometimes', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'phone'     => ['nullable', 'string', 'max:20'],
            'cpf'       => ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($userId)],
            'user_type' => ['sometimes', Rule::in([
                User::TYPE_FUNCIONARIO,
                User::TYPE_DONO_EMPRESA,
                User::TYPE_CLIENTE,
            ])],
            'status' => ['sometimes', Rule::in([
                User::STATUS_ATIVO,
                User::STATUS_INATIVO,
                User::STATUS_PENDENTE,
            ])],
            'avatar' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'nome',
            'email'     => 'e-mail',
            'password'  => 'senha',
            'phone'     => 'telefone',
            'cpf'       => 'CPF',
            'user_type' => 'tipo de usuário',
            'status'    => 'status',
            'avatar'    => 'avatar',
        ];
    }
}
