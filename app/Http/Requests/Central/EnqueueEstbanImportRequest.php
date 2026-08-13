<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class EnqueueEstbanImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manual_dir' => ['nullable', 'string', 'max:500'],
            'ano' => ['required', 'integer', 'min:2012', 'max:2099'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $dir = $this->input('manual_dir');
        if (is_string($dir)) {
            $this->merge(['manual_dir' => str_replace('\\', '/', trim($dir))]);
        }
    }
}
