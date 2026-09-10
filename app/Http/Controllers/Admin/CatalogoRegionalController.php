<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\LicenseNotAvailableException;
use App\Http\Controllers\Controller;
use App\Models\CatalogLicense;
use App\Services\LicenseActivationService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * O que a direção regional liberou para esta câmara.
 *
 * A câmara não edita o conteúdo: escolhe o que ativar e define as datas das turmas.
 */
class CatalogoRegionalController extends Controller
{
    public function __construct(private LicenseActivationService $activation) {}

    public function index(): View
    {
        $tenantId = TenantContext::getTenantId();

        $licencas = CatalogLicense::query()
            ->forTenant($tenantId)
            ->with(['catalogItem.lessons', 'course.courseClasses', 'events', 'director'])
            ->orderByDesc('id')
            ->get();

        return view('admin.catalogo-regional.index', compact('licencas'));
    }

    public function show(CatalogLicense $licenca): View
    {
        $this->assertOwnTenant($licenca);

        $licenca->load(['catalogItem.lessons', 'course.courseClasses', 'events', 'director']);

        return view('admin.catalogo-regional.show', ['licenca' => $licenca]);
    }

    public function storeTurma(Request $request, CatalogLicense $licenca): RedirectResponse
    {
        $this->assertOwnTenant($licenca);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tipo_turma' => ['required', 'in:presencial,online'],
            'max_seats' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'enrollment_start' => ['nullable', 'date'],
            'enrollment_end' => ['nullable', 'date', 'after_or_equal:enrollment_start'],
            'data_inicio' => ['required', 'date'],
            'intervalo_dias' => ['required', 'integer', 'min:1', 'max:180'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'data_inicio.required' => 'Informe a data da primeira aula.',
            'hora_fim.after' => 'O horário de término precisa ser depois do início.',
        ]);

        try {
            $turma = $this->activation->openTurma(
                $licenca,
                TenantContext::getTenantId(),
                $data,
                $request->user()->id
            );
        } catch (LicenseNotAvailableException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.turmas.show', $turma)
            ->with('success', 'Turma criada com as aulas do conteúdo regional. Ajuste as datas se precisar.');
    }

    /**
     * Agenda uma edição da palestra liberada pela direção regional.
     */
    public function storeEvento(Request $request, CatalogLicense $licenca): RedirectResponse
    {
        $this->assertOwnTenant($licenca);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'date_time' => ['required', 'date'],
            'palestrante_nome' => ['nullable', 'string', 'max:255'],
            'max_seats' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'allow_online_registration' => ['nullable', 'boolean'],
            'com_certificado' => ['nullable', 'boolean'],
            'registration_starts_at' => ['nullable', 'date'],
            'registration_ends_at' => ['nullable', 'date', 'after_or_equal:registration_starts_at'],
            'zipcode' => ['nullable', 'string', 'max:9'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
        ], [
            'date_time.required' => 'Informe a data e a hora da palestra.',
        ]);

        try {
            $evento = $this->activation->openEvento(
                $licenca,
                TenantContext::getTenantId(),
                $data,
                $request->user()->id
            );
        } catch (LicenseNotAvailableException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.eventos.edit', $evento)
            ->with('success', 'Palestra agendada. Ajuste local, inscrições e certificado se precisar.');
    }

    private function assertOwnTenant(CatalogLicense $licenca): void
    {
        abort_unless((int) $licenca->tenant_id === (int) TenantContext::getTenantId(), 404);
    }
}
