<?php

namespace App\Http\Requests\Escola;

use App\Rules\CpfRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreEventParticipantRequest extends FormRequest
{
    protected $errorBag = 'eventParticipant';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $digits = static fn (?string $v): string => preg_replace('/\D/', '', (string) $v) ?? '';

        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'cpf' => $digits($this->input('cpf')),
            'cidade' => trim((string) $this->input('cidade', '')),
            'presente' => $this->boolean('presente'),
            'palestra_ids' => collect($this->input('palestra_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->filled('student_id')) {
            $tenantId = auth()->user()?->tenant_id;

            return [
                'student_id' => [
                    'required',
                    'integer',
                    Rule::exists('students', 'id')->when(
                        $tenantId !== null,
                        fn ($rule) => $rule->where('tenant_id', $tenantId)
                    ),
                ],
                'presente' => ['sometimes', 'boolean'],
                'palestra_ids' => ['nullable', 'array'],
                'palestra_ids.*' => ['integer'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'sexo' => ['required', 'in:masculino,feminino,outro,nao_informado'],
            'cpf' => ['required', 'string', 'size:11', new CpfRule],
            'cidade' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'presente' => ['sometimes', 'boolean'],
            'palestra_ids' => ['nullable', 'array'],
            'palestra_ids.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'aluno',
            'name' => 'nome completo',
            'email' => 'e-mail',
            'birth_date' => 'data de nascimento',
            'sexo' => 'sexo',
            'cpf' => 'CPF',
            'cidade' => 'cidade',
            'password' => 'senha',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Selecione um aluno na busca antes de inscrever.',
            'student_id.exists' => 'Aluno não encontrado nesta escola.',
        ];
    }
}
