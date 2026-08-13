<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'curriculum_id' => ['nullable', 'integer', 'exists:curricula,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'class_date' => ['required', 'date'],
            'status' => ['required', 'in:presente,falta,justificada'],
        ];
    }
}
