<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'tenant_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . ($userId ?? 'NULL')],
            'phone' => ['nullable', 'string', 'max:20'],
            'user_type' => ['required', 'in:' . implode(',', array_keys(UserType::options()))],
            'role' => ['required', 'in:tenant_admin,tenant_manager,tenant_user'],
            'status' => ['required', 'in:' . implode(',', array_keys(UserStatus::options()))],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está registrado.',
            'user_type.required' => 'O tipo de usuário é obrigatório.',
            'user_type.in' => 'O tipo de usuário selecionado é inválido.',
            'role.required' => 'A função/papel é obrigatória.',
            'role.in' => 'A função/papel selecionada é inválida.',
            'status.required' => 'A situação é obrigatória.',
            'status.in' => 'A situação selecionada é inválida.',
        ];
    }
}
