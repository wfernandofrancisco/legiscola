<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'certificado_disponivel_ate' => $this->filled('certificado_disponivel_ate')
                ? $this->input('certificado_disponivel_ate')
                : null,
            'satisfaction_survey_id' => $this->filled('satisfaction_survey_id')
                ? $this->input('satisfaction_survey_id')
                : null,
            'satisfaction_survey_required' => $this->boolean('satisfaction_survey_required'),
        ]);
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'tipo_turma' => ['required', 'in:presencial,online'],
            'max_seats' => ['required', 'integer', 'min:1'],
            'enrollment_start' => ['required', 'date'],
            'enrollment_end' => ['required', 'date', 'after_or_equal:enrollment_start'],
            'certificado_disponivel_ate' => ['nullable', 'date'],
            'satisfaction_survey_id' => [
                'nullable',
                'integer',
                Rule::exists('satisfaction_surveys', 'id')->where(fn ($q) => $q->where('tenant_id', auth()->user()->tenant_id)),
            ],
            'satisfaction_survey_required' => ['boolean'],
            'status' => ['required', 'in:cadastrado,inscricao,em_andamento,concluido,cancelado'],
            'schedules' => ['required_if:tipo_turma,presencial', 'array', 'min:1'],
            'schedules.*.weekday' => ['required_with:schedules.*.start_time,schedules.*.end_time', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['required_with:schedules.*.weekday,schedules.*.end_time', 'date_format:H:i'],
            'schedules.*.end_time' => ['required_with:schedules.*.weekday,schedules.*.start_time', 'date_format:H:i'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => [
                'integer',
                Rule::exists('teachers', 'id')->where(fn ($q) => $q->where('tenant_id', auth()->user()->tenant_id)),
            ],
        ];
    }
}
