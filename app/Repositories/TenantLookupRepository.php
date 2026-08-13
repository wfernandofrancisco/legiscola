<?php

namespace App\Repositories;

use App\Contracts\Repositories\TenantLookupRepositoryInterface;
use App\Models\Tenant;
use App\Support\CagedMunicipioCodigo;
use App\Support\ComexMunicipioCodigo;
use Illuminate\Support\Collection;

class TenantLookupRepository implements TenantLookupRepositoryInterface
{
    public function activeForCnpjProcess(): Collection
    {
        return Tenant::query()
            ->where('status', Tenant::STATUS_ATIVO)
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_ibge_municipio']);
    }

    public function activeForCagedProcess(): Collection
    {
        return Tenant::query()
            ->where('status', Tenant::STATUS_ATIVO)
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_municipio_caged']);
    }

    public function activeIbgeMunicipioToTenantIdMap(): array
    {
        $map = [];

        foreach ($this->activeForCnpjProcess() as $tenant) {
            $digits = preg_replace('/\D/', '', (string) ($tenant->codigo_ibge_municipio ?? '')) ?? '';
            $ibge = strlen($digits) >= 7 ? substr($digits, -7) : '';
            if ($ibge === '' || isset($map[$ibge])) {
                continue;
            }
            $map[$ibge] = (int) $tenant->id;
        }

        return $map;
    }

    public function activeCagedMunicipioToTenantIdMap(): array
    {
        $map = [];

        foreach ($this->activeForCagedProcess() as $tenant) {
            $key = CagedMunicipioCodigo::normalize((string) ($tenant->codigo_municipio_caged ?? ''));
            if ($key === null || isset($map[$key])) {
                continue;
            }
            $map[$key] = (int) $tenant->id;
        }

        return $map;
    }

    public function activeForComexProcess(): Collection
    {
        return Tenant::query()
            ->where('status', Tenant::STATUS_ATIVO)
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_importacao_exportacao']);
    }

    public function activeComexMunicipioToTenantIdMap(): array
    {
        $map = [];

        foreach ($this->activeForComexProcess() as $tenant) {
            $key = ComexMunicipioCodigo::normalize((string) ($tenant->codigo_importacao_exportacao ?? ''));
            if ($key === null || isset($map[$key])) {
                continue;
            }
            $map[$key] = (int) $tenant->id;
        }

        return $map;
    }

    public function activeForEstbanProcess(): Collection
    {
        return Tenant::query()
            ->where('status', Tenant::STATUS_ATIVO)
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_municipio_estban', 'codigo_ibge_municipio']);
    }

    public function activeEstbanMunicipioToTenantIdMap(): array
    {
        $lookup = $this->activeEstbanImportMunicipioLookup();

        return $lookup['ibge7'] + $lookup['codmun'];
    }

    public function activeEstbanImportMunicipioLookup(): array
    {
        $byIbge7 = [];
        $byCodmun = [];

        foreach ($this->activeForEstbanProcess() as $tenant) {
            $id = (int) $tenant->id;

            $ibgeRaw = trim((string) ($tenant->codigo_ibge_municipio ?? ''));
            $ibgeDigits = $this->digitsOnlyForEstbanKey($ibgeRaw);
            $ibge7 = strlen($ibgeDigits) >= 7 ? substr($ibgeDigits, -7) : '';
            if ($ibge7 !== '' && ! isset($byIbge7[$ibge7])) {
                $byIbge7[$ibge7] = $id;
            }

            $estbanRaw = trim((string) ($tenant->codigo_municipio_estban ?? ''));
            $estbanDigits = $this->digitsOnlyForEstbanKey($estbanRaw);
            if ($estbanDigits !== '' && ! isset($byCodmun[$estbanDigits])) {
                $byCodmun[$estbanDigits] = $id;
            }

            if ($estbanDigits !== '' && strlen($estbanDigits) >= 7) {
                $e7 = substr($estbanDigits, -7);
                if (! isset($byIbge7[$e7])) {
                    $byIbge7[$e7] = $id;
                }
            }
        }

        return [
            'ibge7' => $byIbge7,
            'codmun' => $byCodmun,
        ];
    }

    private function digitsOnlyForEstbanKey(string $raw): string
    {
        $s = trim($raw);
        if ($s === '') {
            return '';
        }
        if (preg_match('/^([+-]?\d{1,20})[.,](\d+)$/', $s, $m) && $m[2] !== '' && strspn($m[2], '0') === strlen($m[2])) {
            $s = $m[1];
        }
        $s = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $s) ?? $s;

        return preg_replace('/\D/', '', $s) ?? '';
    }
}
