<?php

namespace App\Http\Requests\Central;

use App\Support\BrazilianStates;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDirectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'ufs' => ['required', 'array', 'min:1'],
            'ufs.*' => ['string', Rule::in(BrazilianStates::codes())],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'ufs' => 'UFs de abrangência',
            'ufs.*' => 'UF',
        ];
    }

    public function messages(): array
    {
        return [
            'ufs.required' => 'Selecione ao menos uma UF — sem UF o diretor não enxerga nenhum cliente.',
            'ufs.min' => 'Selecione ao menos uma UF — sem UF o diretor não enxerga nenhum cliente.',
        ];
    }
}
