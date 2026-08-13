<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', Tenant::STATUS_ATIVO)->count(),
            'pending_cadastro' => Tenant::where('cadastro_status', Tenant::CADASTRO_PENDENTE)->count(),
            'total_users' => User::count(),
            'total_budgets' => Budget::count(),
        ];

        $recent_tenants = Tenant::latest()->limit(5)->get();

        return view('central.dashboard', compact('stats', 'recent_tenants'));
    }
}
