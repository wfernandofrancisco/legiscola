<?php

namespace App\Services;

use App\Enums\CatalogLicensePagamentoStatus;
use App\Models\CatalogLicense;
use App\Models\User;
use App\Support\BrazilianStates;
use App\Support\DirectorContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Recebíveis do diretor a partir das licenças emitidas.
 *
 * Controle simples de acompanhamento: a fonte é o próprio registro da licença, não há
 * lançamento contábil separado. Licença sem valor fica fora das contas.
 */
class DirectorFinanceService
{
    /**
     * Licenças do diretor, restritas à abrangência atual e com valor informado.
     */
    public function query(User $director, ?int $ano = null): Builder
    {
        return CatalogLicense::query()
            ->where('director_user_id', $director->id)
            ->whereIn('tenant_id', DirectorContext::tenantIds())
            ->whereNotNull('valor')
            ->when($ano, fn (Builder $q) => $q->where(
                fn (Builder $sub) => $sub
                    ->whereYear('vencimento_em', $ano)
                    ->orWhere(fn (Builder $s) => $s->whereNull('vencimento_em')->whereYear('created_at', $ano))
            ));
    }

    /**
     * @return Collection<int, CatalogLicense>
     */
    public function licenses(User $director, ?int $ano = null): Collection
    {
        return $this->query($director, $ano)
            ->with(['catalogItem', 'tenant'])
            ->orderByRaw('vencimento_em is null, vencimento_em asc')
            ->get();
    }

    /**
     * @return array<string, float|int>
     */
    public function summary(Collection $licenses): array
    {
        $emAberto = $licenses->filter(fn (CatalogLicense $l) => $l->pagamento_status->isOpen());

        return [
            'contratado' => (float) $licenses->sum(fn (CatalogLicense $l) => (float) $l->valor),
            'recebido' => (float) $licenses
                ->where('pagamento_status', CatalogLicensePagamentoStatus::Pago)
                ->sum(fn (CatalogLicense $l) => (float) $l->valor),
            'em_aberto' => (float) $emAberto->sum(fn (CatalogLicense $l) => (float) $l->valor),
            'vencido' => (float) $emAberto
                ->filter(fn (CatalogLicense $l) => $l->vencimento_em !== null && $l->vencimento_em->isBefore(now()->startOfDay()))
                ->sum(fn (CatalogLicense $l) => (float) $l->valor),
            'licencas' => $licenses->count(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function perUf(Collection $licenses): Collection
    {
        return $licenses
            ->groupBy(fn (CatalogLicense $l) => (string) $l->tenant->estado)
            ->map(fn (Collection $grupo, string $uf) => [
                'uf' => $uf,
                'estado' => BrazilianStates::name($uf) ?? $uf,
            ] + $this->summary($grupo))
            ->sortKeys()
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function perTenant(Collection $licenses): Collection
    {
        return $licenses
            ->groupBy(fn (CatalogLicense $l) => $l->tenant_id)
            ->map(fn (Collection $grupo) => [
                'tenant' => $grupo->first()->tenant,
            ] + $this->summary($grupo))
            ->sortByDesc('em_aberto')
            ->values();
    }

    /**
     * Recebíveis por mês de vencimento. Licença sem vencimento cai em "sem data".
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function perMonth(Collection $licenses): Collection
    {
        return $licenses
            ->groupBy(fn (CatalogLicense $l) => $l->vencimento_em?->format('Y-m') ?? '0000-00')
            ->map(fn (Collection $grupo, string $chave) => [
                'chave' => $chave,
                'rotulo' => $chave === '0000-00'
                    ? 'Sem data de vencimento'
                    : $this->rotuloMes($chave),
            ] + $this->summary($grupo))
            ->sortKeys()
            ->values();
    }

    /**
     * Anos com recebível registrado, para o seletor da tela.
     *
     * @return list<int>
     */
    public function anosDisponiveis(User $director): array
    {
        $anos = CatalogLicense::query()
            ->where('director_user_id', $director->id)
            ->whereIn('tenant_id', DirectorContext::tenantIds())
            ->whereNotNull('valor')
            ->selectRaw('DISTINCT COALESCE(YEAR(vencimento_em), YEAR(created_at)) as ano')
            ->pluck('ano')
            ->map(fn ($ano): int => (int) $ano)
            ->filter()
            ->sortDesc()
            ->values()
            ->all();

        return $anos === [] ? [(int) now()->year] : $anos;
    }

    private function rotuloMes(string $chave): string
    {
        [$ano, $mes] = explode('-', $chave);

        $nomes = [
            1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
        ];

        return ($nomes[(int) $mes] ?? $mes).' de '.$ano;
    }
}
