<?php

namespace App\Models;

use Illuminate\Support\Collection;

/**
 * Agregação de alunos por bairro (somente leitura; não possui tabela própria).
 */
final class AlunoBairro
{
    /**
     * @return Collection<int, object{bairro: string, total: int}>
     */
    public static function agrupadosPorBairro(): Collection
    {
        return Student::query()
            ->whereNotNull('bairro')
            ->where('bairro', '!=', '')
            ->selectRaw('bairro, COUNT(*) as total')
            ->groupBy('bairro')
            ->orderBy('bairro')
            ->get()
            ->map(fn ($row) => (object) [
                'bairro' => (string) $row->bairro,
                'total' => (int) $row->total,
            ]);
    }
}
