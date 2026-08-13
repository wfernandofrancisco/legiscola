<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseClassAnnouncementRequest extends FormRequest
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
            'reference_date' => ['nullable', 'date'],
            'subject' => [
                Rule::requiredIf(fn () => in_array('email', $this->input('channels', []), true)),
                'nullable',
                'string',
                'max:190',
            ],
            'body' => ['required', 'string', 'max:8000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['in:email,sms'],
            'consent_acknowledged' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_acknowledged.accepted' => 'Confirme o compromisso de envio em conformidade com a LGPD.',
            'subject.required_if' => 'Informe o assunto do e-mail quando o canal e-mail estiver marcado.',
        ];
    }
}
