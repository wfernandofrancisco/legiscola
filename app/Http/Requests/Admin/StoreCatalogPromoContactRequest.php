<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogPromoContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^[\d\s()+-]{10,20}$/'],
            'interesse' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'whatsapp' => 'WhatsApp',
            'interesse' => 'descrição do interesse',
        ];
    }

    public function messages(): array
    {
        return [
            'whatsapp.regex' => 'Informe um WhatsApp válido, com DDD.',
        ];
    }
}
