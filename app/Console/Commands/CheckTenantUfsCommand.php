<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\BrazilianStates;
use Illuminate\Console\Command;

/**
 * A abrangência do diretor regional é derivada de tenants.estado.
 *
 * Cliente sem UF (ou com UF inválida) fica invisível para todos os diretores — este comando
 * aponta esses casos antes de o papel ser liberado.
 */
class CheckTenantUfsCommand extends Command
{
    protected $signature = 'diretor:check-ufs';

    protected $description = 'Lista tenants sem UF ou com UF inválida (ficam invisíveis para os diretores regionais)';

    public function handle(): int
    {
        $tenants = Tenant::query()
            ->select(['id', 'name', 'nome_fantasia', 'razao_social', 'cidade', 'estado'])
            ->orderBy('estado')
            ->orderBy('name')
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant cadastrado.');

            return self::SUCCESS;
        }

        $problemas = $tenants->reject(fn (Tenant $t) => BrazilianStates::isValid($t->estado));

        $this->newLine();
        $this->line("Tenants: {$tenants->count()} | sem UF válida: {$problemas->count()}");

        if ($problemas->isNotEmpty()) {
            $this->newLine();
            $this->error('Estes clientes não aparecem para nenhum diretor regional:');
            $this->table(
                ['ID', 'Cliente', 'Cidade', 'UF'],
                $problemas->map(fn (Tenant $t) => [
                    $t->id,
                    $t->display_name,
                    $t->cidade ?: '-',
                    $t->estado ?: '(vazio)',
                ])->all()
            );
        }

        $porUf = $tenants
            ->filter(fn (Tenant $t) => BrazilianStates::isValid($t->estado))
            ->groupBy(fn (Tenant $t) => BrazilianStates::normalize($t->estado))
            ->map->count()
            ->sortKeys();

        if ($porUf->isNotEmpty()) {
            $this->newLine();
            $this->info('Distribuição por UF:');
            $this->table(
                ['UF', 'Estado', 'Clientes'],
                $porUf->map(fn (int $qtd, string $uf) => [$uf, BrazilianStates::name($uf), $qtd])->values()->all()
            );
        }

        return $problemas->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
