<?php

namespace App\Http\Requests\Escola;

use App\Enums\CertificateTipoEmissao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCertificateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tipo_emissao' => ['required', Rule::enum(CertificateTipoEmissao::class)],
            'engine' => ['required', 'in:blade,html,image'],
            'html_template' => ['nullable', 'string'],
            'background_image_path' => ['nullable', 'string', 'max:255'],
            'background_image' => ['nullable', 'file', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
