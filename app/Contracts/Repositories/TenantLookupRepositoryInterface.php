<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface TenantLookupRepositoryInterface
{
    public function activeForCnpjProcess(): Collection;

    /**
     * Tenants ativos com dados para o mapa da importação Caged (campo {@see Tenant::$codigo_municipio_caged}).
     */
    public function activeForCagedProcess(): Collection;

    /**
     * @return array<string, int> Código IBGE do município (7 dígitos) => tenant id (primeiro tenant ativo por código).
     */
    public function activeIbgeMunicipioToTenantIdMap(): array;

    /**
     * Mapa código município Caged (6 ou 7 dígitos, igual ao arquivo) => tenant id, usando {@see Tenant::$codigo_municipio_caged}.
     *
     * @return array<string, int>
     */
    public function activeCagedMunicipioToTenantIdMap(): array;

    /**
     * Tenants ativos com dados para o mapa da importação Comex ({@see Tenant::$codigo_importacao_exportacao}).
     */
    public function activeForComexProcess(): Collection;

    /**
     * Mapa CO_MUN normalizado (7 dígitos) => tenant id, usando {@see Tenant::$codigo_importacao_exportacao}.
     *
     * @return array<string, int>
     */
    public function activeComexMunicipioToTenantIdMap(): array;

    /**
     * Tenants ativos com códigos usados na importação ESTBAN ({@see Tenant::$codigo_municipio_estban} ou fallback {@see Tenant::$codigo_ibge_municipio}).
     */
    public function activeForEstbanProcess(): Collection;

    /**
     * Mapa CO_MUNICIPIO / CODMUN_IBGE (7 dígitos) => tenant id: usa {@see Tenant::$codigo_municipio_estban} se preenchido, senão {@see Tenant::$codigo_ibge_municipio}.
     *
     * @return array<string, int>
     */
    public function activeEstbanMunicipioToTenantIdMap(): array;

    /**
     * Mapas para importação ESTBAN: chaves IBGE (7 dígitos) a partir de {@see Tenant::$codigo_ibge_municipio}
     * e chaves numéricas completas do campo {@see Tenant::$codigo_municipio_estban} (ex.: CODMUN do BCB, qualquer tamanho).
     *
     * @return array{ibge7: array<string, int>, codmun: array<string, int>}
     */
    public function activeEstbanImportMunicipioLookup(): array;
}
