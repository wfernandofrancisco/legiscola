<?php

namespace App\Contracts\Services;

use Illuminate\Contracts\Pagination\Paginator;

interface CnaeSinonimoServiceInterface
{
    /**
     * Paginar sinônimos com filtros
     */
    public function paginate(int $perPage = 15, array $filters = []): Paginator;

    /**
     * Sugerir um novo sinônimo (como tenant)
     */
    public function suggest(array $data): object;

    /**
     * Aprovar uma sugestão (como super_admin)
     */
    public function approve(int $id, array $data = []): object;

    /**
     * Rejeitar uma sugestão (como super_admin)
     */
    public function reject(int $id, array $data = []): object;

    /**
     * Obter sinônimos aprovados para um CNAE
     */
    public function getApprovedForCnae(int $cnaeId): array;

    /**
     * Buscar sinônimos por FULLTEXT
     */
    public function searchByFullText(string $term): array;

    /**
     * Obter sinônimo por ID
     */
    public function findById(int $id): ?object;

    /**
     * Deletar sinônimo
     */
    public function delete(int $id): bool;
}
