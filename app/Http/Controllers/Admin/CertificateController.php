<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CertificateServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\IssueCertificateRequest;
use App\Models\Certificate;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateServiceInterface $service,
        private StudentServiceInterface $studentService
    ) {}

    public function alunoDashboard(): View
    {
        $student = $this->studentService->findByUserId((int) auth()->id());

        return view('users.aluno.dashboard', [
            'student' => $student,
            'grades' => $student?->grades()->with('course')->latest('evaluated_at')->get() ?? collect(),
            'attendances' => $student?->attendances()->with('course')->latest('class_date')->get() ?? collect(),
            'certificates' => $student?->certificates()->with('course')->latest('issued_at')->get() ?? collect(),
        ]);
    }

    public function issue(IssueCertificateRequest $request): RedirectResponse
    {
        $certificate = $this->service->issue($request->validated());

        if ($request->boolean('redirect_to_download')) {
            return redirect()->route('certificados.download', $certificate->validation_hash);
        }

        return back()->with('success', 'Certificado emitido com sucesso.');
    }

    public function validateHash(string $hash): View
    {
        return view('public.certificate-validate', [
            'certificate' => $this->service->findByValidationHash($hash),
        ]);
    }

    public function downloadByHash(string $hash)
    {
        $certificate = $this->service->findByValidationHash($hash)?->loadMissing(['template', 'course', 'event']);
        abort_if(! $certificate, 404);

        $filename = 'certificado-'.Str::limit($certificate->validation_hash, 12, '').'.pdf';

        if (! empty($certificate->pdf_path)) {
            $candidate = storage_path('app/public/'.ltrim((string) $certificate->pdf_path, '/'));
            if (is_file($candidate)) {
                return response()->download($candidate, $filename);
            }
        }

        $html = $this->buildCertificateHtml($certificate);

        return Pdf::loadHTML($html)->setPaper('a4', 'landscape')->download($filename);
    }

    public function revoke(Certificate $certificate): RedirectResponse
    {
        $this->service->revoke($certificate, 'Revogado administrativamente.');

        return back()->with('success', 'Certificado revogado.');
    }

    private function buildCertificateHtml(Certificate $certificate): string
    {
        $tenant = Tenant::query()->find($certificate->tenant_id);
        $studentName = (string) data_get($certificate->snapshot, 'student_name', $certificate->student?->user?->name ?? 'Aluno');
        $palestranteNome = (string) data_get($certificate->snapshot, 'palestrante_nome', $studentName);
        $palestranteCpf = (string) data_get($certificate->snapshot, 'palestrante_cpf', '');
        $isPalestrante = (string) data_get($certificate->snapshot, 'tipo_emissao', '') === 'palestrante'
            || (bool) data_get($certificate->snapshot, 'is_palestrante', false);
        $courseName = (string) data_get(
            $certificate->snapshot,
            'course_name',
            $certificate->course?->name
                ?? $certificate->event?->title
                ?? 'Curso'
        );
        $isEventCertificate = $certificate->event_id !== null;
        $eventoNome = $isEventCertificate
            ? (string) data_get($certificate->snapshot, 'evento_nome', $courseName)
            : '';
        $workload = (string) data_get($certificate->snapshot, 'workload_hours', '0');
        $conclusionDate = $certificate->issued_at?->format('d/m/Y') ?? now()->format('d/m/Y');
        $tenantCity = trim((string) ($tenant?->cidade ?? ''));
        $tenantState = mb_strtoupper(trim((string) ($tenant?->estado ?? '')));
        $tenantCityState = $tenantCity !== ''
            ? $tenantCity.($tenantState !== '' ? ' - '.$tenantState : '')
            : 'Cidade do Tenant';
        $tenantName = trim((string) ($tenant?->display_name ?? $tenant?->name ?? ''));
        $schoolName = $tenantName !== ''
            ? 'Escola Legislativa da '.$tenantName
            : 'Escola Legislativa da Câmara de Vereadores';

        $displayName = $isPalestrante ? $palestranteNome : $studentName;

        $replacements = [
            '{{aluno_nome}}' => $displayName,
            '{{palestrante_nome}}' => $palestranteNome,
            '{{palestrante_cpf}}' => $palestranteCpf,
            '{{curso_nome}}' => $courseName,
            '{{evento_nome}}' => $eventoNome,
            '{{carga_horaria}}' => $workload.' horas',
            '{{data_conclusao}}' => $conclusionDate,
            '{{cidade}}' => $tenantCityState,
            '{{uf}}' => $tenantState !== '' ? $tenantState : 'UF',
            '{{hash_validacao}}' => $certificate->validation_hash,
            '{{tenant_nome}}' => $tenantName,
            '{{escola_legislativa}}' => $schoolName,
            '@{{aluno_nome}}' => $displayName,
            '@{{palestrante_nome}}' => $palestranteNome,
            '@{{palestrante_cpf}}' => $palestranteCpf,
            '@{{curso_nome}}' => $courseName,
            '@{{evento_nome}}' => $eventoNome,
            '@{{carga_horaria}}' => $workload.' horas',
            '@{{data_conclusao}}' => $conclusionDate,
            '@{{cidade}}' => $tenantCityState,
            '@{{uf}}' => $tenantState !== '' ? $tenantState : 'UF',
            '@{{hash_validacao}}' => $certificate->validation_hash,
            '@{{tenant_nome}}' => $tenantName,
            '@{{escola_legislativa}}' => $schoolName,
        ];

        $templateHtml = (string) ($certificate->template?->html_template ?? '');
        if (trim($templateHtml) !== '') {
            $content = strtr($templateHtml, $replacements);
        } elseif ($isPalestrante) {
            $content = '<div style="text-align:center; padding:80px 60px; font-family: DejaVu Sans, Arial, sans-serif;">
                <p style="font-size:20px; margin-top:40px;">Certificamos que</p>
                <p style="font-size:30px; font-family: DejaVu Serif, Times New Roman, serif; font-style:italic; letter-spacing:1px; font-weight:bold; margin:18px 0;">'.e($palestranteNome).'</p>
                <p style="font-size:18px; line-height:1.6;">atuou como palestrante no evento <strong>'.e($eventoNome !== '' ? $eventoNome : $courseName).'</strong>.</p>
                <p style="font-size:16px; margin-top:22px;">Data de emissão: <strong>'.e($conclusionDate).'</strong>, em <strong>'.e($tenantCityState).'</strong>.</p>
                <p style="margin-top:30px; font-size:14px;">Código de validação: '.e($certificate->validation_hash).'</p>
                <p style="font-size:15px; margin-top:26px;">'.e($schoolName).'</p>
            </div>';
        } elseif ($isEventCertificate) {
            $content = '<div style="text-align:center; padding:80px 60px; font-family: DejaVu Sans, Arial, sans-serif;">
                <p style="font-size:20px; margin-top:40px;">Certificamos que</p>
                <p style="font-size:30px; font-family: DejaVu Serif, Times New Roman, serif; font-style:italic; letter-spacing:1px; font-weight:bold; margin:18px 0;">'.e($studentName).'</p>
                <p style="font-size:18px; line-height:1.6;">participou do evento <strong>'.e($eventoNome !== '' ? $eventoNome : $courseName).'</strong>.</p>
                <p style="font-size:16px; margin-top:22px;">Data de emissão: <strong>'.e($conclusionDate).'</strong>, em <strong>'.e($tenantCityState).'</strong>.</p>
                <p style="margin-top:30px; font-size:14px;">Código de validação: '.e($certificate->validation_hash).'</p>
                <p style="font-size:15px; margin-top:26px;">'.e($schoolName).'</p>
            </div>';
        } else {
            $content = '<div style="text-align:center; padding:80px 60px; font-family: DejaVu Sans, Arial, sans-serif;">
                <p style="font-size:20px; margin-top:40px;">Certificamos que</p>
                <p style="font-size:30px; font-family: DejaVu Serif, Times New Roman, serif; font-style:italic; letter-spacing:1px; font-weight:bold; margin:18px 0;">'.e($studentName).'</p>
                <p style="font-size:18px; line-height:1.6;">concluiu o curso <strong>'.e($courseName).'</strong> com carga horária de <strong>'.e($workload).' horas</strong>.</p>
                <p style="font-size:16px; margin-top:22px;">Conclusão em <strong>'.e($conclusionDate).'</strong>, em <strong>'.e($tenantCityState).'</strong>.</p>
                <p style="margin-top:30px; font-size:14px;">Código de validação: '.e($certificate->validation_hash).'</p>
                <p style="font-size:15px; margin-top:26px;">'.e($schoolName).'</p>
            </div>';
        }

        $backgroundImageTag = '';
        $backgroundPath = (string) ($certificate->template?->background_image_path ?? '');
        if ($backgroundPath !== '') {
            $candidate = storage_path('app/public/'.ltrim($backgroundPath, '/'));
            if (is_file($candidate)) {
                $backgroundImageTag = '<img class="bg-image" src="'.str_replace('\\', '/', $candidate).'" alt="Fundo do certificado">';
            }
        }

        return '<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <style>
                    @page { margin: 0; size: A4 landscape; }
                    html, body { margin: 0; padding: 0; width: 297mm; height: 210mm; font-family: DejaVu Sans, Arial, sans-serif; }
                    .sheet { position: relative; width: 297mm; height: 210mm; overflow: hidden; }
                    .bg-image { position: absolute; top: 0; left: 0; width: 297mm; height: 210mm; z-index: 1; }
                    .content { position: absolute; top: 0; left: 0; width: 297mm; height: 210mm; z-index: 2; padding: 0; box-sizing: border-box; }
                </style>
            </head>
            <body><div class="sheet">'.$backgroundImageTag.'<div class="content">'.$content.'</div></div></body>
            </html>';
    }
}
