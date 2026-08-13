<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class EnqueueCagedImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manual_dir' => ['nullable', 'string', 'max:500'],
            'sync_layout' => ['nullable', 'boolean'],
        ];
    }
}
