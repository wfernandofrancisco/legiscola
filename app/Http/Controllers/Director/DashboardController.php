<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\DirectorInsightsService;
use App\Support\DirectorContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DirectorInsightsService $insights) {}

    public function index(): View
    {
        $rows = $this->insights->perTenant();

        return view('director.dashboard', [
            'ufs' => DirectorContext::ufs(),
            'summary' => $this->insights->summary($rows),
            'grupos' => $this->insights->perUf($rows),
            'topCursos' => $this->insights->topCourses(),
        ]);
    }
}
