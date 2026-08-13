<?php

namespace App\Http\Requests\Professor;

use App\Models\ClassLesson;
use App\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class QuickClassLessonFromSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'is_online' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var CourseClass $turma */
            $turma = $this->route('turma');
            if (! $turma instanceof CourseClass) {
                return;
            }

            $date = (string) $this->input('date');
            $startTime = (string) $this->input('start_time');
            $endTime = (string) $this->input('end_time');
            $isOnline = (bool) $this->boolean('is_online');

            if (! $date || ! $startTime || ! $endTime || $isOnline) {
                return;
            }

            if ($startTime >= $endTime) {
                $validator->errors()->add('end_time', 'O horário de fim deve ser maior que o horário de início.');

                return;
            }

            $hasConflict = ClassLesson::query()
                ->where('course_class_id', $turma->id)
                ->whereDate('date', $date)
                ->where('is_online', false)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add(
                    'start_time',
                    'Já existe aula presencial nesta turma, no mesmo dia, com sobreposição de horário.'
                );
            }
        });
    }
}
