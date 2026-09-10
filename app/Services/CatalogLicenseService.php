<?php

namespace App\Services;

use App\Enums\CatalogItemTipo;
use App\Enums\CatalogLicensePagamentoStatus;
use App\Enums\CatalogLicenseStatus;
use App\Models\CatalogItem;
use App\Models\CatalogLicense;
use App\Models\Tenant;
use App\Models\User;

class CatalogLicenseService
{
    public function create(User $director, CatalogItem $item, Tenant $tenant, array $data): CatalogLicense
    {
        $status = CatalogLicenseStatus::from($data['status']);
        $ehPalestra = $item->tipo === CatalogItemTipo::Palestra;

        return CatalogLicense::create([
            'catalog_item_id' => $item->id,
            'tenant_id' => $tenant->id,
            'director_user_id' => $director->id,
            'status' => $status,
            // A data de liberação marca quando o conteúdo passou a existir para o cliente.
            'liberado_em' => $status->isUsable() ? now() : null,
            'exibir_ate' => $data['exibir_ate'] ?? null,
            // Palestra costuma ser 1 edição; o teto de público fica em max_inscritos.
            'max_turmas' => $ehPalestra
                ? ($data['max_turmas'] ?? 1)
                : ($data['max_turmas'] ?? null),
            'modalidade' => $ehPalestra ? ($data['modalidade'] ?? null) : null,
            'palestra_em' => $ehPalestra ? ($data['palestra_em'] ?? null) : null,
            'max_inscritos' => $ehPalestra ? ($data['max_inscritos'] ?? null) : null,
            'professor_nome' => trim((string) ($data['professor_nome'] ?? '')) ?: null,
            'observacoes' => $data['observacoes'] ?? null,
            'valor' => $data['valor'] ?? null,
            'pagamento_status' => $data['pagamento_status'] ?? CatalogLicensePagamentoStatus::Pendente,
            'vencimento_em' => $data['vencimento_em'] ?? null,
            'forma_pagamento' => $data['forma_pagamento'] ?? null,
        ]);
    }

    public function update(CatalogLicense $license, array $data): CatalogLicense
    {
        $status = CatalogLicenseStatus::from($data['status']);
        $ehPalestra = $license->catalogItem->tipo === CatalogItemTipo::Palestra;

        $license->fill([
            'status' => $status,
            'exibir_ate' => $data['exibir_ate'] ?? null,
            'max_turmas' => $ehPalestra
                ? ($data['max_turmas'] ?? $license->max_turmas ?? 1)
                : ($data['max_turmas'] ?? null),
            'modalidade' => $ehPalestra ? ($data['modalidade'] ?? null) : null,
            'palestra_em' => $ehPalestra ? ($data['palestra_em'] ?? null) : null,
            'max_inscritos' => $ehPalestra ? ($data['max_inscritos'] ?? null) : null,
            'professor_nome' => trim((string) ($data['professor_nome'] ?? '')) ?: null,
            'observacoes' => $data['observacoes'] ?? null,
            'valor' => $data['valor'] ?? null,
            'pagamento_status' => $data['pagamento_status'] ?? CatalogLicensePagamentoStatus::Pendente,
            'vencimento_em' => $data['vencimento_em'] ?? null,
            'pago_em' => $data['pago_em'] ?? null,
            'forma_pagamento' => $data['forma_pagamento'] ?? null,
            'nota_fiscal_numero' => $data['nota_fiscal_numero'] ?? null,
            'nota_fiscal_emitida_em' => $data['nota_fiscal_emitida_em'] ?? null,
        ]);

        // Marcar como pago sem informar a data é o caso comum; a data do dia evita linha sem referência.
        if ($license->pagamento_status === CatalogLicensePagamentoStatus::Pago && $license->pago_em === null) {
            $license->pago_em = now()->toDateString();
        }

        if ($status->isUsable() && $license->liberado_em === null) {
            $license->liberado_em = now();
        }

        $license->save();

        return $license;
    }

    public function changeStatus(CatalogLicense $license, CatalogLicenseStatus $status): CatalogLicense
    {
        $license->status = $status;

        if ($status->isUsable() && $license->liberado_em === null) {
            $license->liberado_em = now();
        }

        $license->save();

        return $license;
    }
}
