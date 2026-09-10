<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\DirectorInsightsService;
use App\Support\BrazilianStates;
use App\Support\DirectorContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function __construct(private DirectorInsightsService $insights) {}

    public function index(Request $request): View
    {
        $rows = $this->insights->perTenant();

        $uf = BrazilianStates::normalize((string) $request->query('uf'));
        if ($uf !== '' && in_array($uf, DirectorContext::ufs(), true)) {
            $rows = $rows->filter(fn (array $row): bool => $row['tenant']->estado === $uf)->values();
        } else {
            $uf = '';
        }

        $busca = trim((string) $request->query('q'));
        if ($busca !== '') {
            $rows = $rows->filter(
                fn (array $row): bool => str_contains(
                    mb_strtolower($row['tenant']->display_name.' '.$row['tenant']->cidade),
                    mb_strtolower($busca)
                )
            )->values();
        }

        return view('director.clientes.index', [
            'rows' => $rows,
            'ufs' => DirectorContext::ufs(),
            'ufSelecionada' => $uf,
            'busca' => $busca,
        ]);
    }

    /**
     * A checagem de abrangência vive no serviço (tenantDetail aborta com 404 fora da região),
     * então não dá para chegar aqui com o id de um cliente de outro diretor.
     */
    public function show(Tenant $cliente): View
    {
        return view('director.clientes.show', $this->insights->tenantDetail($cliente));
    }
}
