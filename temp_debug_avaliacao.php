<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\OrcamentoSolicitacao;
use App\Support\TenantContext;

// Without tenant context
$row1 = OrcamentoSolicitacao::query()
    ->where('empresa_id', 43220)
    ->whereNotNull('avaliacao_estrelas')
    ->toBase()
    ->selectRaw('AVG(avaliacao_estrelas) as media, COUNT(*) as total')
    ->first();
echo "Without tenant context: " . json_encode($row1) . PHP_EOL;

// With tenant context = 2
TenantContext::set(2);
$row2 = OrcamentoSolicitacao::query()
    ->where('empresa_id', 43220)
    ->whereNotNull('avaliacao_estrelas')
    ->toBase()
    ->selectRaw('AVG(avaliacao_estrelas) as media, COUNT(*) as total')
    ->first();
echo "With tenant_id=2: " . json_encode($row2) . PHP_EOL;

// Check aceitaOrcamentos
$empresa = App\Models\Empresa::find(43220);
$empresa->load(['override']);
echo "override: " . json_encode($empresa->override) . PHP_EOL;
echo "aceitaOrcamentos: " . ($empresa->override?->deseja_receber_orcamentos === true ? 'true' : 'false') . PHP_EOL;
