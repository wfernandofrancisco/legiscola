<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CertificateTemplateServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreCertificateTemplateRequest;
use App\Http\Requests\Escola\UpdateCertificateTemplateRequest;
use App\Models\CertificateTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateTemplateController extends Controller
{
    public function __construct(private CertificateTemplateServiceInterface $service) {}

    public function index(Request $request): View
    {
        $templates = CertificateTemplate::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('engine'), fn ($query) => $query->where('engine', (string) $request->input('engine')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Templates de certificado'],
        ];

        return view('admin.certificate-templates.index', compact('templates', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Templates de certificado', 'href' => route('admin.templates-certificado.index')],
            ['label' => 'Novo template'],
        ];

        return view('admin.certificate-templates.create', compact('breadcrumbs'));
    }

    public function edit(CertificateTemplate $certificateTemplate): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Templates de certificado', 'href' => route('admin.templates-certificado.index')],
            ['label' => 'Editar template'],
        ];

        return view('admin.certificate-templates.edit', compact('certificateTemplate', 'breadcrumbs'));
    }

    public function store(StoreCertificateTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['background_image_path'] = $this->resolveBackgroundImagePath($request);
        $this->service->create($data);
        return back()->with('success', 'Template de certificado criado.');
    }

    public function update(UpdateCertificateTemplateRequest $request, CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $data = $request->validated();
        $data['background_image_path'] = $this->resolveBackgroundImagePath($request, $certificateTemplate);
        $this->service->update($certificateTemplate, $data);
        return back()->with('success', 'Template atualizado.');
    }

    public function destroy(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $this->service->delete($certificateTemplate);
        return redirect()->route('admin.templates-certificado.index')->with('success', 'Template removido.');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'engine' => ['nullable', 'in:blade,html,image'],
            'html_template' => ['nullable', 'string'],
            'background_image' => ['nullable', 'file', 'image', 'max:5120'],
            'background_image_path' => ['nullable', 'string', 'max:255'],
        ]);

        $backgroundPath = null;
        if ($request->hasFile('background_image')) {
            $backgroundPath = $request->file('background_image')->getRealPath();
        } elseif ($request->filled('background_image_path')) {
            $candidate = storage_path('app/public/' . ltrim((string) $request->input('background_image_path'), '/'));
            if (is_file($candidate)) {
                $backgroundPath = $candidate;
            }
        }

        $html = $this->renderPreviewHtml(
            htmlTemplate: (string) $request->input('html_template', ''),
            backgroundPath: $backgroundPath
        );

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

        $filename = 'preview-template-' . Str::slug((string) $request->input('name', 'certificado')) . '.pdf';

        return $pdf->stream($filename);
    }

    private function resolveBackgroundImagePath(Request $request, ?CertificateTemplate $template = null): ?string
    {
        if ($request->hasFile('background_image')) {
            return $request->file('background_image')->store('certificate-templates', 'public');
        }

        return (string) ($request->input('background_image_path') ?: $template?->background_image_path ?: '');
    }

    private function renderPreviewHtml(string $htmlTemplate, ?string $backgroundPath = null): string
    {
        $tenantCity = (string) (auth()->user()?->tenant?->cidade ?? '');
        $tenantState = (string) (auth()->user()?->tenant?->estado ?? '');
        $tenantName = trim((string) (auth()->user()?->tenant?->display_name ?? auth()->user()?->tenant?->name ?? ''));
        $tenantSchoolName = $tenantName !== ''
            ? 'Escola Legislativa da ' . $tenantName
            : 'Escola Legislativa da Câmara de Vereadores';
        $tenantCityState = trim($tenantCity) !== ''
            ? trim($tenantCity) . (trim($tenantState) !== '' ? ' - ' . mb_strtoupper(trim($tenantState)) : '')
            : 'Cidade do Tenant';
        $conclusionDate = now()->format('d/m/Y');

        $replacements = [
            '{{aluno_nome}}' => 'ALUNO TESTE DA SILVA',
            '{{palestrante_nome}}' => 'PALESTRANTE TESTE',
            '{{palestrante_cpf}}' => '000.000.000-00',
            '{{curso_nome}}' => 'Curso de Formação Cidadã',
            '{{evento_nome}}' => 'Seminário de Participação Popular',
            '{{carga_horaria}}' => '40 horas',
            '{{hash_validacao}}' => strtoupper(Str::random(16)),
            '{{data_conclusao}}' => $conclusionDate,
            '{{cidade}}' => $tenantCityState,
            '{{uf}}' => trim($tenantState) !== '' ? mb_strtoupper(trim($tenantState)) : 'UF',
            '{{tenant_nome}}' => $tenantName !== '' ? $tenantName : 'Câmara de Vereadores',
            '{{escola_legislativa}}' => $tenantSchoolName,
            '@{{aluno_nome}}' => 'ALUNO TESTE DA SILVA',
            '@{{palestrante_nome}}' => 'PALESTRANTE TESTE',
            '@{{palestrante_cpf}}' => '000.000.000-00',
            '@{{curso_nome}}' => 'Curso de Formação Cidadã',
            '@{{evento_nome}}' => 'Seminário de Participação Popular',
            '@{{carga_horaria}}' => '40 horas',
            '@{{hash_validacao}}' => strtoupper(Str::random(16)),
            '@{{data_conclusao}}' => $conclusionDate,
            '@{{cidade}}' => $tenantCityState,
            '@{{uf}}' => trim($tenantState) !== '' ? mb_strtoupper(trim($tenantState)) : 'UF',
            '@{{tenant_nome}}' => $tenantName !== '' ? $tenantName : 'Câmara de Vereadores',
            '@{{escola_legislativa}}' => $tenantSchoolName,
        ];

        $content = trim($htmlTemplate) !== '' ? strtr($htmlTemplate, $replacements) : '
            <div style="text-align:center; padding:80px 60px; font-family: DejaVu Sans, Arial, sans-serif;">
                <h1 style="font-size:34px; margin-bottom:24px;">CERTIFICADO</h1>
                <p style="font-size:20px;">Certificamos que</p>
                <p style="font-size:30px; font-family: DejaVu Serif, Times New Roman, serif; font-style:italic; letter-spacing:1px; font-weight:bold; margin:18px 0;">ALUNO TESTE DA SILVA</p>
                <p style="font-size:18px; line-height:1.6;">
                    concluiu o curso <strong>Curso de Formação Cidadã</strong> com carga horária de
                    <strong>40 horas</strong>.
                </p>
                <p style="font-size:16px; margin-top:22px;">
                    Conclusão em <strong>' . $conclusionDate . '</strong>, em <strong>' . $tenantCityState . '</strong>.
                </p>
                <p style="font-size:15px; margin-top:26px;">' . $tenantSchoolName . '</p>
                <p style="margin-top:30px; font-size:14px;">Código de validação: ' . strtoupper(Str::random(16)) . '</p>
            </div>
        ';

        $backgroundImageTag = '';
        if ($backgroundPath && is_file($backgroundPath)) {
            $backgroundSrc = str_replace('\\', '/', $backgroundPath);
            $backgroundImageTag = '<img class="bg-image" src="' . $backgroundSrc . '" alt="Fundo do certificado">';
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
            <body><div class="sheet">' . $backgroundImageTag . '<div class="content">' . $content . '</div></div></body>
            </html>';
    }
}
