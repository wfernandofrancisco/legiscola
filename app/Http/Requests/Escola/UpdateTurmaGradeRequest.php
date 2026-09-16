<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTurmaGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade' => ['required', 'array', 'min:1'],
            'grade.*.class_lesson_id' => ['nullable', 'integer'],
            'grade.*.course_lesson_id' => ['nullable', 'integer', 'exists:course_lessons,id'],
            'grade.*.catalog_lesson_id' => ['nullable', 'integer', 'exists:catalog_lessons,id'],
            'grade.*.date' => ['nullable', 'date'],
            'grade.*.start_time' => ['nullable', 'date_format:H:i'],
            'grade.*.end_time' => ['nullable', 'date_format:H:i'],
            'grade.*.is_online' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade.required' => 'Informe a grade das aulas desta turma.',
            'grade.*.date.required' => 'Informe a data de cada aula.',
        ];
    }
}
