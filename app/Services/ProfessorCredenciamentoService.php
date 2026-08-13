<?php

namespace App\Services;

use App\Contracts\Repositories\ProfessorCredenciamentoRepositoryInterface;
use App\Contracts\Services\ProfessorCredenciamentoServiceInterface;
use App\Models\ProfessorCredenciamento;
use App\Models\ProfessorCredenciamentoAnexo;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfessorCredenciamentoService implements ProfessorCredenciamentoServiceInterface
{
    public function __construct(private ProfessorCredenciamentoRepositoryInterface $repository) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($perPage, $search);
    }

    public function create(array $data): ProfessorCredenciamento
    {
        return DB::transaction(function () use ($data): ProfessorCredenciamento {
            $credenciamento = $this->repository->create([
                'tenant_id' => TenantContext::getTenantId(),
                'titulo' => $data['titulo'],
                'ano_referencia' => $data['ano_referencia'] ?? null,
                'texto' => $data['texto'],
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->persistAnexos($credenciamento, $data);

            return $credenciamento;
        });
    }

    public function update(ProfessorCredenciamento $credenciamento, array $data): bool
    {
        return DB::transaction(function () use ($credenciamento, $data): bool {
            if (! empty($data['delete_anexos'])) {
                $anexos = $credenciamento->anexos()->whereIn('id', $data['delete_anexos'])->get();
                foreach ($anexos as $anexo) {
                    Storage::disk('public')->delete((string) $anexo->arquivo_path);
                    $this->repository->deleteAnexo($anexo);
                }
            }

            $updated = $this->repository->update($credenciamento, [
                'titulo' => $data['titulo'],
                'ano_referencia' => $data['ano_referencia'] ?? null,
                'texto' => $data['texto'],
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->persistAnexos($credenciamento, $data);

            return $updated;
        });
    }

    public function delete(ProfessorCredenciamento $credenciamento): bool
    {
        return DB::transaction(function () use ($credenciamento): bool {
            foreach ($credenciamento->anexos as $anexo) {
                Storage::disk('public')->delete((string) $anexo->arquivo_path);
                $this->repository->deleteAnexo($anexo);
            }

            return $this->repository->delete($credenciamento);
        });
    }

    public function deleteAnexo(ProfessorCredenciamentoAnexo $anexo): bool
    {
        Storage::disk('public')->delete((string) $anexo->arquivo_path);
        return $this->repository->deleteAnexo($anexo);
    }

    private function persistAnexos(ProfessorCredenciamento $credenciamento, array $data): void
    {
        if (empty($data['anexos']) || ! is_array($data['anexos'])) {
            return;
        }

        $tenantId = TenantContext::getTenantId();
        foreach ($data['anexos'] as $index => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('professores-credenciamentos/' . $tenantId, 'public');
            $title = trim((string) ($data['anexo_titulos'][$index] ?? ''));

            $this->repository->createAnexo([
                'tenant_id' => $tenantId,
                'professor_credenciamento_id' => $credenciamento->id,
                'titulo' => $title !== '' ? $title : 'Anexo ' . ($index + 1),
                'arquivo_path' => $path,
                'ordem' => $index,
            ]);
        }
    }
}
