<?php

namespace App\Http\Requests\Portal;

use App\Rules\TurnstileRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePortalContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'message' => ['required', 'string', 'max:5000'],
            'cf-turnstile-response' => [new TurnstileRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'message' => 'mensagem',
        ];
    }
}
