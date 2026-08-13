<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseClassQuizWindowsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'windows' => ['present', 'array'],
            'windows.*.quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'windows.*.opens_at' => ['nullable', 'date'],
            'windows.*.closes_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $windows = $this->input('windows', []);
        if (! is_array($windows)) {
            $this->merge(['windows' => []]);

            return;
        }

        foreach ($windows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach (['opens_at', 'closes_at'] as $key) {
                if (array_key_exists($key, $row) && $row[$key] === '') {
                    $windows[$i][$key] = null;
                }
            }
        }

        $this->merge(['windows' => $windows]);
    }
}
