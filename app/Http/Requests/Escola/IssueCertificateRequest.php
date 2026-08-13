<?php

namespace App\Http\Requests\Escola;

use App\Enums\CertificateTipoEmissao;
use App\Models\CertificateTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class IssueCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],
            'snapshot' => ['required', 'array'],
            'pdf_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $hasCourse = $this->filled('course_id');
            $hasEvent = $this->filled('event_id');

            if ($hasCourse === $hasEvent) {
                $v->errors()->add('course_id', 'Informe o curso (emitir pela turma) ou o evento — apenas um deles.');

                return;
            }

            $templateId = $this->input('certificate_template_id');
            if (! $templateId) {
                return;
            }

            $tpl = CertificateTemplate::query()->find($templateId);
            if (! $tpl) {
                return;
            }

            if ($hasEvent && $tpl->tipo_emissao !== CertificateTipoEmissao::Evento) {
                $v->errors()->add('certificate_template_id', 'O template precisa ser do tipo «evento» para certificado de evento.');
            }

            if ($hasCourse && $tpl->tipo_emissao !== CertificateTipoEmissao::Curso) {
                $v->errors()->add('certificate_template_id', 'O template precisa ser do tipo «curso» para certificado de turma.');
            }
        });
    }
}
