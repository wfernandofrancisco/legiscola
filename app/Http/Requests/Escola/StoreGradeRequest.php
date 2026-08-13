<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
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
            'score' => ['required', 'numeric', 'min:0', 'max:10'],
            'max_score' => ['required', 'numeric', 'min:1'],
            'evaluated_at' => ['required', 'date'],
        ];
    }
}
