<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class CompleteCourseClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'minimum_attendance' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
