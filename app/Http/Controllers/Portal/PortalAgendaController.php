<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Portal\PortalAgendaService;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalAgendaController extends Controller
{
    public function __construct(
        private PortalAgendaService $agendaService,
    ) {}

    public function index(Request $request): View
    {
        $tenant = Tenant::query()->findOrFail((int) TenantContext::getTenantId());

        $mes = $request->query('mes');
        $month = CarbonImmutable::now()->startOfMonth();
        if (is_string($mes) && preg_match('/^(\d{4})-(\d{2})$/', $mes, $m)) {
            $y = (int) $m[1];
            $mo = (int) $m[2];
            if ($y >= 2000 && $y <= 2100 && $mo >= 1 && $mo <= 12) {
                try {
                    $month = CarbonImmutable::parse(sprintf('%04d-%02d-01', $y, $mo))->startOfMonth();
                } catch (\Throwable) {
                    $month = CarbonImmutable::now()->startOfMonth();
                }
            }
        }

        return view('portal.agenda.index', array_merge(
            ['tenant' => $tenant],
            $this->agendaService->calendarData($month),
        ));
    }
}
