<?php

namespace App\Http\Requests\Escola\Concerns;

use App\Models\EventPalestra;
use App\Rules\CpfRule;
use Illuminate\Validation\Rule;

trait ValidatesEventPalestras
{
    /**
     * @return array<string, mixed>
     */
    protected function palestraRules(): array
    {
        return [
            'palestras' => ['nullable', 'array', 'max:30'],
            'palestras.*.id' => ['nullable', 'integer'],
            'palestras.*.title' => ['required_with:palestras', 'string', 'max:255'],
            'palestras.*.date_time' => ['required_with:palestras', 'date'],
            'palestras.*.max_seats' => ['nullable', 'integer', 'min:1'],
            'palestras.*.com_certificado' => ['sometimes', 'boolean'],
            'palestras.*.palestrante_nome' => ['nullable', 'string', 'max:255'],
            'palestras.*.palestrante_cpf' => ['nullable', 'string', 'size:11', new CpfRule],
            'palestras.*.palestrante_senha' => ['nullable', 'string', 'min:6', 'max:64'],
        ];
    }

    protected function preparePalestras(): void
    {
        $rows = $this->input('palestras', []);
        if (! is_array($rows)) {
            return;
        }

        $prepared = [];
        foreach (array_values($rows) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $cpf = preg_replace('/\D/', '', (string) ($row['palestrante_cpf'] ?? '')) ?: null;
            $title = trim((string) ($row['title'] ?? ''));
            $date = $row['date_time'] ?? null;
            $maxSeats = $row['max_seats'] ?? null;
            $maxSeats = ($maxSeats === '' || $maxSeats === null) ? null : (int) $maxSeats;

            if ($title === '' && ! filled($date) && ! filled($row['palestrante_nome'] ?? null) && empty($row['id'])) {
                continue;
            }

            $prepared[] = [
                'id' => filled($row['id'] ?? null) ? (int) $row['id'] : null,
                'title' => $title,
                'date_time' => $date,
                'max_seats' => $maxSeats,
                'com_certificado' => ! empty($row['com_certificado']),
                'palestrante_nome' => filled($row['palestrante_nome'] ?? null) ? trim((string) $row['palestrante_nome']) : null,
                'palestrante_cpf' => $cpf !== '' ? $cpf : null,
                'palestrante_senha' => filled($row['palestrante_senha'] ?? null) ? $row['palestrante_senha'] : null,
                'ordem' => $index + 1,
            ];
        }

        $this->merge(['palestras' => $prepared]);
    }

    public function withPalestraValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            foreach ($this->input('palestras', []) as $index => $row) {
                if (! is_array($row) || ! filled($row['palestrante_nome'] ?? null)) {
                    continue;
                }

                $existing = null;
                if (! empty($row['id'])) {
                    $existing = EventPalestra::query()->find((int) $row['id']);
                }

                if (! filled($row['palestrante_senha'] ?? null) && ! filled($existing?->palestrante_senha)) {
                    $v->errors()->add(
                        "palestras.{$index}.palestrante_senha",
                        'Informe a senha do palestrante (mínimo 6 caracteres) ou use «Gerar senha».'
                    );
                }
            }
        });
    }
}
