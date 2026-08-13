<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class EnqueueComexImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manual_dir' => ['nullable', 'string', 'max:500'],
            'co_ano' => ['required', 'integer', 'min:1997', 'max:2100'],
            'sync_paises' => ['nullable', 'boolean'],
            'sync_sh' => ['nullable', 'boolean'],
        ];
    }
}
