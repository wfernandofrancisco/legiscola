<?php

namespace App\Support;

/**
 * Unidades federativas do Brasil.
 *
 * Usado no cadastro de clientes (tenants.estado) e na abrangência do diretor regional,
 * que enxerga os tenants cujo estado está entre as UFs atribuídas a ele.
 */
class BrazilianStates
{
    public const UFS = [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ];

    /**
     * @return array<string, string> UF => nome do estado
     */
    public static function all(): array
    {
        return self::UFS;
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::UFS);
    }

    /**
     * Opções de select mostrando sigla e nome (ex.: 'SP' => 'SP — São Paulo').
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::UFS as $uf => $nome) {
            $options[$uf] = $uf.' — '.$nome;
        }

        return $options;
    }

    public static function name(?string $uf): ?string
    {
        return self::UFS[self::normalize($uf)] ?? null;
    }

    public static function isValid(?string $uf): bool
    {
        return isset(self::UFS[self::normalize($uf)]);
    }

    public static function normalize(?string $uf): string
    {
        return mb_strtoupper(trim((string) $uf));
    }
}
