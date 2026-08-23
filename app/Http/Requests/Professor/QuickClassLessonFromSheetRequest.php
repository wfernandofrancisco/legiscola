<?php

namespace App\Http\Requests\Professor;

use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Support\ClockTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class QuickClassLessonFromSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_time' => ClockTime::toHis($this->input('start_time')),
            'end_time' => ClockTime::toHis($this->input('end_time')),
            'is_online' => $this->boolean('is_online'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s'],
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
            $startMinutes = ClockTime::minutes($this->input('start_time'));
            $endMinutes = ClockTime::minutes($this->input('end_time'));
            $startHis = ClockTime::toHis($this->input('start_time'));
            $endHis = ClockTime::toHis($this->input('end_time'));
            $isOnline = (bool) $this->boolean('is_online');

            if (! $date || $startMinutes === null || $endMinutes === null || $isOnline) {
                return;
            }

            if ($startMinutes >= $endMinutes) {
                $validator->errors()->add('end_time', 'O horário de fim deve ser maior que o horário de início.');

                return;
            }

            $hasConflict = ClassLesson::query()
                ->where('course_class_id', $turma->id)
                ->whereDate('date', $date)
                ->where('is_online', false)
                ->where('start_time', '<', $endHis)
                ->where('end_time', '>', $startHis)
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add(
                    'start_time',
                    'Já existe aula presencial nesta turma, no mesmo dia, com sobreposição de horário. Altere o horário ou marque como aula online.'
                );
            }
        });
    }
}
