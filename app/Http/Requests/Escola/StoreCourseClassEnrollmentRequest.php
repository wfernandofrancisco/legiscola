<?php

namespace App\Http\Requests\Escola;

use App\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseClassEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
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
            'status' => ['nullable', 'in:inscrito,cursando,desistido,concluido,baixa_presenca'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'aluno',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Selecione um aluno na lista de busca antes de matricular.',
            'student_id.exists' => 'Aluno não encontrado neste cliente.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        $turma = $this->route('turma');

        if ($turma instanceof CourseClass) {
            return route('admin.turmas.show', ['turma' => $turma, 'tab' => 'matriculas']);
        }

        return parent::getRedirectUrl();
    }
}
