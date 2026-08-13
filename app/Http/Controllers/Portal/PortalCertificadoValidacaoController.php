<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Services\CertificateServiceInterface;
use App\Http\Controllers\Controller;
use App\Rules\TurnstileRule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalCertificadoValidacaoController extends Controller
{
    public function __construct(
        private CertificateServiceInterface $certificates
    ) {}

    public function create(): View
    {
        return view('portal.certificados.validar', [
            'certificate' => null,
            'consulted' => false,
            'codigoDigitado' => '',
        ]);
    }

    public function store(Request $request): View
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:200'],
            'cf-turnstile-response' => [new TurnstileRule],
        ], [], [
            'codigo' => 'código',
        ]);

        $codigo = trim((string) $request->input('codigo', ''));
        $certificate = $codigo !== ''
            ? $this->certificates->findByValidationHash($codigo)
            : null;

        return view('portal.certificados.validar', [
            'certificate' => $certificate,
            'consulted' => true,
            'codigoDigitado' => $codigo,
        ]);
    }
}
