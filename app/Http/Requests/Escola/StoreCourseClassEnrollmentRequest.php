<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseClassEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['nullable', 'in:inscrito,cursando,desistido,concluido,baixa_presenca'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
