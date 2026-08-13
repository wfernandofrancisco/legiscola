<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'workload_hours' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:rascunho,ativo,inativo,arquivado'],
        ];
    }
}
