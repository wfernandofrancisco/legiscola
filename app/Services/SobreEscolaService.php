<?php

namespace App\Services;

use App\Contracts\Repositories\SobreEscolaRepositoryInterface;
use App\Contracts\Services\SobreEscolaServiceInterface;
use App\Models\SobreEscola;
use App\Models\SobreEscolaEixo;
use App\Models\SobreEscolaPessoa;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SobreEscolaService implements SobreEscolaServiceInterface
{
    public function __construct(private SobreEscolaRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $data): SobreEscola
    {
        return DB::transaction(function () use ($data): SobreEscola {
            $sobreEscola = $this->repository->create([
                'tenant_id' => TenantContext::getTenantId(),
                'institucional' => $data['institucional'] ?? null,
                'objetivos' => $data['objetivos'] ?? null,
                'quem_somos' => $data['quem_somos'] ?? null,
                'projeto_pedagogico' => $data['projeto_pedagogico'] ?? null,
                'legislacao' => $data['legislacao'] ?? null,
            ]);

            $this->syncEixos($sobreEscola, $data);
            $this->syncPessoas($sobreEscola, $data);

            return $sobreEscola;
        });
    }

    public function update(SobreEscola $sobreEscola, array $data): bool
    {
        return DB::transaction(function () use ($sobreEscola, $data): bool {
            $updated = $this->repository->update($sobreEscola, [
                'institucional' => $data['institucional'] ?? null,
                'objetivos' => $data['objetivos'] ?? null,
                'quem_somos' => $data['quem_somos'] ?? null,
                'projeto_pedagogico' => $data['projeto_pedagogico'] ?? null,
                'legislacao' => $data['legislacao'] ?? null,
            ]);

            $this->syncEixos($sobreEscola, $data);
            $this->syncPessoas($sobreEscola, $data);

            return $updated;
        });
    }

    public function delete(SobreEscola $sobreEscola): bool
    {
        foreach ($sobreEscola->pessoas as $pessoa) {
            if ($pessoa->foto_path) {
                Storage::disk('public')->delete($pessoa->foto_path);
            }
        }

        return $this->repository->delete($sobreEscola);
    }

    private function syncEixos(SobreEscola $sobreEscola, array $data): void
    {
        $tenantId = TenantContext::getTenantId();
        $sobreEscola->eixos()->delete();

        $titles = $data['eixo_titulos'] ?? [];
        $descriptions = $data['eixo_descricoes'] ?? [];

        foreach ($titles as $index => $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }

            SobreEscolaEixo::query()->create([
                'tenant_id' => $tenantId,
                'sobre_escola_id' => $sobreEscola->id,
                'titulo' => $title,
                'descricao' => trim((string) ($descriptions[$index] ?? '')) ?: null,
                'ordem' => $index,
            ]);
        }
    }

    private function syncPessoas(SobreEscola $sobreEscola, array $data): void
    {
        $tenantId = TenantContext::getTenantId();

        if (! empty($data['delete_pessoas']) && is_array($data['delete_pessoas'])) {
            $toDelete = $sobreEscola->pessoas()->whereIn('id', $data['delete_pessoas'])->get();
            foreach ($toDelete as $pessoa) {
                if ($pessoa->foto_path) {
                    Storage::disk('public')->delete($pessoa->foto_path);
                }
                $pessoa->delete();
            }
        }

        $nomes = $data['pessoa_nomes'] ?? [];
        $cargos = $data['pessoa_cargos'] ?? [];
        $fotos = $data['pessoa_fotos'] ?? [];

        foreach ($nomes as $index => $nome) {
            $nome = trim((string) $nome);
            $cargo = trim((string) ($cargos[$index] ?? ''));
            $foto = $fotos[$index] ?? null;

            if ($nome === '' || $cargo === '') {
                continue;
            }

            $fotoPath = null;
            if ($foto) {
                $fotoPath = $foto->store('sobre-escola/pessoas/' . $tenantId, 'public');
            }

            SobreEscolaPessoa::query()->create([
                'tenant_id' => $tenantId,
                'sobre_escola_id' => $sobreEscola->id,
                'nome' => $nome,
                'cargo' => $cargo,
                'foto_path' => $fotoPath,
                'ordem' => $index,
            ]);
        }
    }
}
