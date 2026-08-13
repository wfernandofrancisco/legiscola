<?php

namespace App\Http\Requests\Escola;

use App\Models\ClassLesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreClassLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_class_id' => ['required', 'exists:course_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'is_online' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'url'],
            'material_url' => ['nullable', 'url'],
            'material_file' => [
                'nullable',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,txt,png,jpg,jpeg,webp',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $courseClassId = (int) $this->input('course_class_id');
            $date = (string) $this->input('date');
            $startTime = (string) $this->input('start_time');
            $endTime = (string) $this->input('end_time');
            $isOnline = (bool) $this->boolean('is_online');

            if (! $courseClassId || ! $date || ! $startTime || ! $endTime || $isOnline) {
                return;
            }

            if ($startTime >= $endTime) {
                $validator->errors()->add('end_time', 'O horário de fim deve ser maior que o horário de início.');
                return;
            }

            $hasConflict = ClassLesson::query()
                ->where('course_class_id', $courseClassId)
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
