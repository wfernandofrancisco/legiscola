<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\EventEnrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalEventoController extends Controller
{
    public function __construct(
        private PortalCatalogRepositoryInterface $catalog,
    ) {}

    public function index(Request $request): View
    {
        return view('portal.eventos.index', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'eventos' => $this->catalog->paginateEvents((int) $request->integer('per_page') ?: 12),
        ]);
    }

    public function show(int $evento): View
    {
        $model = $this->catalog->findEvent($evento);
        abort_if($model === null, 404);
        abort_unless((int) $model->tenant_id === TenantContext::getTenantId(), 404);
        $model->loadCount('enrollments');

        $user = auth()->user();
        $student = $user ? Student::query()->where('user_id', $user->id)->first() : null;
        $podeInscricaoPortal = $user
            && $user->isTenantUser()
            && $student !== null;
        $jaInscrito = $podeInscricaoPortal
            && EventEnrollment::query()
                ->where('event_id', $model->id)
                ->where('student_id', $student->id)
                ->exists();

        return view('portal.eventos.show', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'evento' => $model,
            'inscricaoPortalAberta' => $model->isOnlineRegistrationOpen(),
            'podeInscricaoPortal' => $podeInscricaoPortal,
            'jaInscritoNoEvento' => $jaInscrito,
        ]);
    }

    public function enroll(int $evento, EnrollmentServiceInterface $enrollmentService): RedirectResponse
    {
        $model = $this->catalog->findEvent($evento);
        abort_if($model === null, 404);
        abort_unless((int) $model->tenant_id === TenantContext::getTenantId(), 404);

        $user = auth()->user();
        abort_unless($user && $user->isTenantUser(), 403);

        $student = Student::query()->where('user_id', $user->id)->first();
        if (! $student) {
            return redirect()
                ->route('portal.eventos.show', $evento)
                ->with('warning', 'Perfil de aluno não encontrado. Procure a secretaria.');
        }

        try {
            $enrollmentService->inscreverEmEvento((int) $student->id, $evento);
        } catch (ValidationException $e) {
            return redirect()
                ->route('portal.eventos.show', $evento)
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('portal.eventos.show', $evento)
            ->with('success', 'Inscrição confirmada com sucesso.');
    }
}
