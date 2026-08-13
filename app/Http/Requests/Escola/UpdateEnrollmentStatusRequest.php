<?php

namespace App\Http\Requests\Escola;

use App\Models\Enrollment;
use App\Models\Student;
use App\Support\CourseClassAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateEnrollmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_status' => ['required', 'in:inscrito,cursando,desistido,concluido,baixa_presenca'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $status = (string) $this->input('enrollment_status');
            $turmaStatus = (string) ($this->route('turma')?->status ?? '');
            $turma = $this->route('turma');
            $enrollment = $this->route('enrollment');

            if ($status === 'concluido' && $turmaStatus !== 'concluido') {
                $validator->errors()->add(
                    'enrollment_status',
                    'Só é permitido marcar aluno como concluído quando a turma estiver com status concluído.'
                );
            }

            if ($status !== 'concluido' || ! $turma || ! $enrollment) {
                return;
            }

            $minimumAttendance = 75;
            $student = Student::query()->find((int) $enrollment->student_id);
            if (! $student) {
                return;
            }

            $attendancePercent = CourseClassAttendance::studentPercent($student, $turma);

            if ($attendancePercent === null) {
                $validator->errors()->add(
                    'enrollment_status',
                    'Cadastre aulas nesta turma para poder avaliar frequência antes de marcar o aluno como concluído.'
                );

                return;
            }

            if ($attendancePercent < $minimumAttendance) {
                $validator->errors()->add(
                    'enrollment_status',
                    "Só é permitido concluir aluno com frequência mínima de {$minimumAttendance}% (por aulas da turma)."
                );
            }
        });
    }
}
