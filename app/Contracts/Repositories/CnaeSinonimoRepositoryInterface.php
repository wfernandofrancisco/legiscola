<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\Paginator;

interface CnaeSinonimoRepositoryInterface
{
    /**
     * Paginar com filtros
     */
    public function paginate(int $perPage = 15, array $filters = []): Paginator;

    /**
     * Encontrar por ID
     */
    public function findById(int $id): ?object;

    /**
     * Criar novo sinônimo
     */
    public function create(array $data): object;

    /**
     * Atualizar sinônimo
     */
    public function update(int $id, array $data): object;

    /**
     * Deletar sinônimo
     */
    public function delete(int $id): bool;

    /**
     * Buscar com FULLTEXT
     */
    public function searchByFullText(string $term, int $perPage = 15): Paginator;

    /**
     * Obter por status
     */
    public function getByStatus(int $status, int $perPage = 15): Paginator;

    /**
     * Obter sinônimos aprovados para um CNAE
     */
    public function getApprovedForCnae(int $cnaeId): array;

    /**
     * Obter sinônimos aguardando aprovação
     */
    public function getPending(int $perPage = 15): Paginator;

    /**
     * Contar pendentes
     */
    public function countPending(): int;
}
