<?php

namespace App\Services\TenantPortal;

use App\Models\Cnae;
use App\Models\CnaeSinonimo;
use App\Models\Empresa;
use App\Models\EmpresaOverride;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TenantPortalMapService
{
    /**
     * @return array{cnaesOptions: Collection, bairros: Collection, markers: array<int, array<string, mixed>>, totalFiltradas: int, comCoordenadas: int, sinonimosDestaque: Collection}
     */
    public function buildMapPageData(Request $request, int $tenantId): array
    {
        $cnaesOptions = Cnae::query()
            ->sharedWithTenant()
            ->where('situacao', true)
            ->whereNotNull('codigo')
            ->where('codigo', '!=', '')
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'descricao']);

        $exprBairroMapa = Empresa::expressaoSqlSomenteBairroNormalizado();

        $bairros = Empresa::query()
            ->selectRaw("({$exprBairroMapa}) as bairro_normalizado_lista")
            ->whereRaw("({$exprBairroMapa}) IS NOT NULL")
            ->distinct()
            ->orderBy('bairro_normalizado_lista')
            ->pluck('bairro_normalizado_lista');

        $markers = [];
        $totalFiltradas = 0;
        $comCoordenadas = 0;

        if ($request->boolean('aplicar')) {
            $validated = $request->validate([
                'cnaes' => ['nullable', 'array'],
                'cnaes.*' => ['nullable', 'string', 'max:12'],
                'sinonimo' => ['nullable', 'string', 'max:120'],
                'data_inicio' => ['nullable', 'date'],
                'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
                'bairro' => ['nullable', 'string', 'max:120'],
                'porte_empresa' => ['nullable', 'string', 'max:4'],
            ]);

            $codes = collect($validated['cnaes'] ?? [])
                ->filter()
                ->map(fn (string $c) => $this->normalizeCnae7($c))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $codesFromSinonimo = [];
            if (! empty($validated['sinonimo'])) {
                $codesFromSinonimo = $this->normalizedCnaeCodesFromSinonimoSearch((string) $validated['sinonimo']);
            }

            $allCodes = collect($codes)->merge($codesFromSinonimo)->unique()->values()->all();

            if ($allCodes === []) {
                return [
                    'cnaesOptions' => $cnaesOptions,
                    'bairros' => $bairros,
                    'markers' => [],
                    'totalFiltradas' => 0,
                    'comCoordenadas' => 0,
                    'sinonimosDestaque' => collect(),
                    'validationMessage' => 'Informe ao menos um CNAE válido ou um sinônimo que encontre CNAEs cadastrados.',
                ];
            }

            $query = Empresa::query();
            $this->applyEmpresaCnaeCodesFilter($query, $allCodes);

            $query
                ->where('situacao_cadastral', '02')
                ->when(! empty($validated['data_inicio']), fn ($q) => $q->whereDate('data_situacao_cadastral', '>=', $validated['data_inicio']))
                ->when(! empty($validated['data_fim']), fn ($q) => $q->whereDate('data_situacao_cadastral', '<=', $validated['data_fim']))
                ->when(! empty($validated['bairro']), function ($q) use ($validated) {
                    $term = trim((string) $validated['bairro']);
                    $expr = Empresa::expressaoSqlSomenteBairroNormalizado();
                    $q->whereRaw("({$expr}) like ?", ['%'.$term.'%']);
                })
                ->when(! empty($validated['porte_empresa']), fn ($q) => $q->where('porte_empresa', $validated['porte_empresa']));

            $totalFiltradas = (clone $query)->count();

            $markers = $query
                ->with('override')
                ->where(function ($q) {
                    $q->where(function ($w) {
                        $w->whereNotNull('empresas.latitude')
                            ->whereNotNull('empresas.longitude');
                    })->orWhereHas('override', function ($o) {
                        $o->whereNotNull('latitude')->whereNotNull('longitude');
                    });
                })
                ->orderBy('razao_social')
                ->get([
                    'id',
                    'cnpj',
                    'razao_social',
                    'nome_fantasia',
                    'latitude',
                    'longitude',
                    'tipo_logradouro',
                    'logradouro',
                    'numero',
                    'complemento',
                    'bairro',
                    'bairro_normalizado',
                    'cep',
                    'uf',
                    'situacao_cadastral',
                    'data_situacao_cadastral',
                    'cnae_fiscal_principal',
                ])
                ->map(fn (Empresa $e) => $this->mapMarkerPayload($e))
                ->filter()
                ->values()
                ->all();

            $comCoordenadas = count($markers);
        }

        $sinonimosDestaque = CnaeSinonimo::query()
            ->approved()
            ->with('cnae:id,codigo,descricao')
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get(['id', 'cnae_id', 'sinonimo']);

        return [
            'cnaesOptions' => $cnaesOptions,
            'bairros' => $bairros,
            'markers' => $markers,
            'totalFiltradas' => $totalFiltradas,
            'comCoordenadas' => $comCoordenadas,
            'sinonimosDestaque' => $sinonimosDestaque,
            'validationMessage' => null,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function aggregateCounts(): array
    {
        $ativas = Empresa::query()->where('situacao_cadastral', '02')->count();
        $comGeo = Empresa::query()->where(function ($q) {
            $q->where(function ($w) {
                $w->whereNotNull('latitude')->whereNotNull('longitude');
            })->orWhereHas('override', function ($o) {
                $o->whereNotNull('latitude')->whereNotNull('longitude');
            });
        })->count();

        $setores = [
            ['label' => 'Alimentação',   'prefixes' => ['56'],     'color' => 'emerald', 'icon' => 'food'],
            ['label' => 'Comércio',       'prefixes' => ['47'],     'color' => 'blue',    'icon' => 'store'],
            ['label' => 'Beleza',         'prefixes' => ['9602'],   'color' => 'pink',    'icon' => 'beauty'],
            ['label' => 'Saúde',          'prefixes' => ['86', '87'], 'color' => 'red',     'icon' => 'health'],
            ['label' => 'Tecnologia',     'prefixes' => ['62', '63'], 'color' => 'violet',  'icon' => 'tech'],
            ['label' => 'Construção',     'prefixes' => ['41', '42', '43'], 'color' => 'orange', 'icon' => 'build'],
        ];

        $setorCounts = array_map(function (array $setor) {
            $q = Empresa::query()->where('situacao_cadastral', '02');
            $q->where(function ($inner) use ($setor) {
                foreach ($setor['prefixes'] as $prefix) {
                    $inner->orWhere('cnae_fiscal_principal', 'like', $prefix.'%');
                }
            });
            $setor['total'] = $q->count();

            return $setor;
        }, $setores);

        return [
            'empresas_total' => Empresa::query()->count(),
            'empresas_ativas' => $ativas,
            'empresas_com_geo' => $comGeo,
            'cnaes_ativos' => Cnae::query()->sharedWithTenant()->where('situacao', true)->count(),
            'setores' => $setorCounts,
        ];
    }

    /**
     * CNAEs cuja palavra-chave (sinônimo) contém $term entre sinônimos aprovados.
     *
     * @return list<string> códigos de 7 dígitos normalizados
     */
    public function normalizedCnaeCodesFromSinonimoSearch(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $pattern = '%'.$term.'%';
        $cnaeIds = CnaeSinonimo::query()
            ->approved()
            ->where('sinonimo', 'like', $pattern)
            ->pluck('cnae_id')
            ->unique()
            ->all();

        if ($cnaeIds === []) {
            return [];
        }

        return Cnae::query()
            ->whereIn('id', $cnaeIds)
            ->whereNotNull('codigo')
            ->where('codigo', '!=', '')
            ->pluck('codigo')
            ->map(fn ($c) => $this->normalizeCnae7((string) $c))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Builder<Empresa>  $query
     * @param  list<string>  $codes  CNAEs já normalizados (7 dígitos)
     */
    public function applyEmpresaCnaeCodesFilter(Builder $query, array $codes): void
    {
        if ($codes === []) {
            return;
        }

        $query->where(function (Builder $outer) use ($codes) {
            foreach ($codes as $i => $code) {
                $group = function (Builder $inner) use ($code): void {
                    $inner->whereRaw('TRIM(cnae_fiscal_principal) = ?', [$code])
                        ->orWhere('cnae_fiscal_principal', 'like', $code.'%')
                        ->orWhere('cnae_fiscal_secundaria', 'like', '%'.$code.'%');
                };
                if ($i === 0) {
                    $outer->where($group);
                } else {
                    $outer->orWhere($group);
                }
            }
        });
    }

    public function normalizeCnae7(string $raw): ?string
    {
        $d = preg_replace('/\D/', '', $raw) ?? '';
        if (strlen($d) < 4) {
            return null;
        }

        return str_pad(substr($d, 0, 7), 7, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapMarkerPayload(Empresa $e): ?array
    {
        $o = $e->override;
        $hasOverride = $o !== null;

        $latO = $hasOverride && $o->latitude !== null && $o->longitude !== null;
        if ($latO) {
            $lat = (float) $o->latitude;
            $lng = (float) $o->longitude;
        } elseif ($e->latitude !== null && $e->longitude !== null) {
            $lat = (float) $e->latitude;
            $lng = (float) $e->longitude;
        } else {
            return null;
        }

        $label = 'Empresa';
        if ($hasOverride && $this->filledString($o->nome_fantasia)) {
            $label = trim((string) $o->nome_fantasia);
        } elseif ($this->filledString($e->nome_fantasia ?? '')) {
            $label = trim((string) $e->nome_fantasia);
        } elseif ($this->filledString($e->razao_social ?? '')) {
            $label = trim((string) $e->razao_social);
        }

        $contato = null;
        if ($hasOverride) {
            if ($this->filledString($o->whatsapp)) {
                $contato = 'WhatsApp: '.$o->whatsapp;
            } elseif ($this->filledString($o->telefone)) {
                $contato = 'Tel: '.$o->telefone;
            }
        }

        $endereco = $hasOverride
            ? $this->formatEnderecoLinhaOverridePrimeiro($o, $e)
            : $this->formatEnderecoLinhaEmpresa($e);

        $bairro = $hasOverride && $this->filledString($o->bairro)
            ? $o->bairro
            : (($this->filledString($e->bairro_normalizado ?? null))
                ? trim((string) $e->bairro_normalizado)
                : null);

        return [
            'lat' => $lat,
            'lng' => $lng,
            'label' => $label,
            'cnpj' => $e->cnpj,
            'contato' => $contato,
            'bairro' => $bairro,
            'endereco' => $endereco,
            'situacao' => $e->situacao_cadastral_label,
            'data_situacao' => $e->data_situacao_cadastral?->format('d/m/Y'),
            'cnae' => $e->cnae_fiscal_principal,
            'url' => route('portal.empresas.show', $e),
        ];
    }

    private function filledString(?string $v): bool
    {
        return $v !== null && trim($v) !== '';
    }

    private function formatEnderecoLinhaEmpresa(Empresa $e): string
    {
        $bairroLinha = $this->filledString($e->bairro_normalizado ?? null)
            ? trim((string) $e->bairro_normalizado)
            : null;

        $parts = array_values(array_filter([
            trim(((string) ($e->tipo_logradouro ?? '')).' '.((string) ($e->logradouro ?? ''))),
            $e->numero,
            $e->complemento,
            $bairroLinha,
            $e->cep,
            $e->uf,
        ], fn ($p) => $p !== null && (is_string($p) ? trim($p) !== '' : true)));

        return implode(', ', $parts);
    }

    private function formatEnderecoLinhaOverridePrimeiro(EmpresaOverride $o, Empresa $e): string
    {
        $fromOverride = array_values(array_filter([
            trim(((string) ($o->tipo_logradouro ?? '')).' '.((string) ($o->logradouro ?? ''))),
            $o->numero,
            $o->complemento,
            $o->bairro,
            $o->cep,
            $o->uf,
        ], fn ($p) => $p !== null && (is_string($p) ? trim($p) !== '' : true)));

        if ($fromOverride !== []) {
            return implode(', ', $fromOverride);
        }

        return $this->formatEnderecoLinhaEmpresa($e);
    }
}
