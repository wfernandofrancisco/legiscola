<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseClassRequest extends FormRequest
{
    use Concerns\CourseClassFormMessages;
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
            'grade' => ['nullable', 'array'],
            'grade.*.course_lesson_id' => ['nullable', 'integer', 'exists:course_lessons,id'],
            'grade.*.catalog_lesson_id' => ['nullable', 'integer', 'exists:catalog_lessons,id'],
            'grade.*.date' => ['nullable', 'date'],
            'grade.*.start_time' => ['nullable', 'date_format:H:i'],
            'grade.*.end_time' => ['nullable', 'date_format:H:i'],
            'grade.*.is_online' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $course = \App\Models\Course::query()->find($this->integer('course_id'));
            if (! $course) {
                return;
            }

            $grade = collect($this->input('grade', []));
            $scheduled = $grade->filter(fn ($row) => filled($row['date'] ?? null) && filled($row['start_time'] ?? null) && filled($row['end_time'] ?? null));

            if ($course->isFromCatalog()) {
                $course->loadMissing('catalogItem.lessons');
                $expected = $course->catalogItem?->lessons->pluck('id')->all() ?? [];
                if ($expected === []) {
                    return;
                }
                $given = $scheduled->pluck('catalog_lesson_id')->map(fn ($id) => (int) $id)->all();
                foreach ($expected as $id) {
                    if (! in_array((int) $id, $given, true)) {
                        $validator->errors()->add('grade', 'Monte a grade com data e horário de todas as aulas do curso.');

                        return;
                    }
                }

                return;
            }

            $expected = $course->lessons()->pluck('id')->all();
            if ($expected === []) {
                return;
            }

            $given = $scheduled->pluck('course_lesson_id')->map(fn ($id) => (int) $id)->all();
            foreach ($expected as $id) {
                if (! in_array((int) $id, $given, true)) {
                    $validator->errors()->add('grade', 'Monte a grade com data e horário de todas as aulas do curso.');

                    return;
                }
            }
        });
    }
}
