<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\CatalogLicense;
use App\Services\DirectorFinanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceiroController extends Controller
{
    public function __construct(private DirectorFinanceService $finance) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CatalogLicense::class);

        $director = $request->user();
        $anos = $this->finance->anosDisponiveis($director);
        $ano = in_array($request->integer('ano'), $anos, true) ? $request->integer('ano') : $anos[0];

        $licencas = $this->finance->licenses($director, $ano);

        return view('director.financeiro.index', [
            'ano' => $ano,
            'anos' => $anos,
            'licencas' => $licencas,
            'resumo' => $this->finance->summary($licencas),
            'porUf' => $this->finance->perUf($licencas),
            'porCamara' => $this->finance->perTenant($licencas),
            'porMes' => $this->finance->perMonth($licencas),
        ]);
    }
}
