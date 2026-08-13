<?php

namespace App\Http\Requests\Aluno;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlunoCadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Student::query()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Student|null $student */
        $student = Student::query()->where('user_id', $this->user()->id)->first();
        $userId = $this->user()->id;
        $studentId = $student?->id ?? 0;

        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                Rule::unique('students', 'email')->ignore($studentId),
            ],
            'sexo' => ['nullable', 'string', 'max:20', Rule::in(['masculino', 'feminino', 'outro', 'nao_informado'])],
            'celular' => ['nullable', 'string', 'max:32'],
            'telefone' => ['nullable', 'string', 'max:32'],
            'cep' => ['nullable', 'string', 'max:12'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:32'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'uf' => ['nullable', 'string', 'max:2'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
