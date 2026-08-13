<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_online_registration' => $this->boolean('allow_online_registration'),
            'com_certificado' => $this->boolean('com_certificado'),
            'registration_starts_at' => $this->filled('registration_starts_at') ? $this->input('registration_starts_at') : null,
            'registration_ends_at' => $this->filled('registration_ends_at') ? $this->input('registration_ends_at') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'allow_online_registration' => ['boolean'],
            'com_certificado' => ['boolean'],
            'registration_starts_at' => [
                Rule::requiredIf($this->boolean('allow_online_registration')),
                'nullable',
                'date',
            ],
            'registration_ends_at' => array_values(array_filter([
                Rule::requiredIf($this->boolean('allow_online_registration')),
                'nullable',
                'date',
                $this->boolean('allow_online_registration') ? 'after:registration_starts_at' : null,
            ])),
            'max_seats' => ['nullable', 'integer', 'min:0'],
            'date_time' => ['required', 'date'],
            'zipcode' => ['nullable', 'string', 'max:9'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
