<?php

namespace App\Http\Requests\Responsible;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_schedule_id' => ['required', 'integer', 'exists:class_schedules,id'],
            'presencas' => ['required', 'array', 'min:1'],
            'presencas.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'presencas.*.is_present' => ['required', 'boolean'],
        ];
    }
}
