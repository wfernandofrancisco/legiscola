<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Services\CertificateServiceInterface;
use App\Enums\CertificateTipoEmissao;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Rules\CpfRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PortalEventoPalestranteCertificadoController extends Controller
{
    public function __construct(
        private CertificateServiceInterface $certificateService
    ) {}

    public function create(Event $evento): View|RedirectResponse
    {
        if (! $evento->hasSpeakerCertificateSetup()) {
            abort(404);
        }

        return view('portal.eventos.certificado-palestrante', [
            'event' => $evento,
            'hasTemplate' => CertificateTemplate::latestActiveForEmission(CertificateTipoEmissao::Palestrante) !== null,
        ]);
    }

    public function store(Request $request, Event $evento): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if (! $evento->hasSpeakerCertificateSetup()) {
            abort(404);
        }

        $template = CertificateTemplate::latestActiveForEmission(CertificateTipoEmissao::Palestrante);
        if (! $template) {
            return back()->withInput()->with('error', 'Ainda não há template de certificado ativo para palestrante. Contate a organização.');
        }

        if (! $evento->isCertificateAccessOpen()) {
            return back()->withInput()->with('error', 'O prazo para emissão/download deste certificado encerrou.');
        }

        $request->merge([
            'cpf' => preg_replace('/\D/', '', (string) $request->input('cpf', '')) ?: '',
        ]);

        $data = $request->validate([
            'cpf' => ['required', 'string', 'size:11', new CpfRule],
            'senha' => ['required', 'string', 'min:6', 'max:64'],
        ], [
            'cpf.required' => 'Informe o CPF.',
            'senha.required' => 'Informe a senha fornecida pela organização.',
        ]);

        $cpf = (string) $data['cpf'];

        if (filled($evento->palestrante_cpf) && $evento->palestrante_cpf !== $cpf) {
            return back()->withInput()->withErrors(['cpf' => 'CPF não confere com o cadastrado para este palestrante.']);
        }

        if (! Hash::check((string) $data['senha'], (string) $evento->palestrante_senha)) {
            return back()->withInput()->withErrors(['senha' => 'Senha incorreta.']);
        }

        $certificate = Certificate::query()
            ->where('event_id', $evento->id)
            ->whereNull('student_id')
            ->where('status', 'issued')
            ->where(function ($q): void {
                $q->where('snapshot->tipo_emissao', 'palestrante')
                    ->orWhere('snapshot->is_palestrante', true);
            })
            ->latest('id')
            ->first();

        $snapshot = [
            'tipo_emissao' => 'palestrante',
            'is_palestrante' => true,
            'student_name' => $evento->palestrante_nome,
            'palestrante_nome' => $evento->palestrante_nome,
            'palestrante_cpf' => $cpf,
            'course_name' => $evento->title,
            'evento_nome' => $evento->title,
            'event_id' => $evento->id,
            'workload_hours' => 0,
        ];

        if (! $certificate) {
            $certificate = $this->certificateService->issue([
                'tenant_id' => $evento->tenant_id,
                'student_id' => null,
                'course_id' => null,
                'event_id' => $evento->id,
                'certificate_template_id' => $template->id,
                'snapshot' => $snapshot,
            ]);
        } else {
            $certificate->update([
                'certificate_template_id' => $template->id,
                'snapshot' => $snapshot,
            ]);
        }

        return app(AdminCertificateController::class)->downloadByHash($certificate->validation_hash);
    }
}
