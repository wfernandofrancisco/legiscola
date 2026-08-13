<?php

namespace App\Services;

use App\Enums\EmpresaResponsibleClaimStatus;
use App\Models\EmpresaResponsibleClaim;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmpresaResponsibleClaimService
{
    /**
     * Regista pedido de vínculo do utilizador à empresa (CNPJ) no tenant actual.
     *
     * @throws \InvalidArgumentException CNPJ inválido, duplicado pendente, ou CNPJ inexistente na base local.
     */
    public function createForUser(
        User $user,
        string $cnpj,
        ?string $razaoSocialInformada,
        ?string $mensagem
    ): void {
        $cnpj = preg_replace('/\D/', '', $cnpj) ?? '';
        if (strlen($cnpj) !== 14) {
            throw new \InvalidArgumentException('CNPJ inválido.');
        }

        if (! $this->cnpjChecksumValid($cnpj)) {
            throw new \InvalidArgumentException('CNPJ inválido (dígitos verificadores).');
        }

        $tenantId = TenantContext::getTenantId() ?? $user->tenant_id;
        if ($tenantId === null) {
            throw new \InvalidArgumentException('Contexto do município (tenant) não encontrado.');
        }

        if ($user->tenant_id === null) {
            $user->forceFill(['tenant_id' => $tenantId])->save();
        } elseif ((int) $user->tenant_id !== (int) $tenantId) {
            throw new \InvalidArgumentException('Utilizador não pertence a este portal.');
        }

        $empresaId = null;
        if (Schema::hasTable('empresas')) {
            $row = DB::table('empresas')
                ->where('tenant_id', $tenantId)
                ->where('cnpj', $cnpj)
                ->first();

            if ($row === null) {
                throw new \InvalidArgumentException('Este CNPJ não está cadastrado como empresa neste portal. Solicite o cadastro da empresa à administração.');
            }

            $empresaId = (int) $row->id;
        }

        $duplicate = EmpresaResponsibleClaim::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('cnpj', $cnpj)
            ->where('status', EmpresaResponsibleClaimStatus::Pendente)
            ->exists();

        if ($duplicate) {
            throw new \InvalidArgumentException('Já existe uma solicitação pendente para este CNPJ.');
        }

        EmpresaResponsibleClaim::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'empresa_id' => $empresaId,
            'cnpj' => $cnpj,
            'razao_social_informada' => $razaoSocialInformada,
            'mensagem' => $mensagem,
            'status' => EmpresaResponsibleClaimStatus::Pendente,
        ]);
    }

    private function cnpjChecksumValid(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights1[$i];
        }
        $r = $sum % 11;
        $d1 = $r < 2 ? 0 : 11 - $r;
        if ((int) $cnpj[12] !== $d1) {
            return false;
        }

        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights2[$i];
        }
        $r = $sum % 11;
        $d2 = $r < 2 ? 0 : 11 - $r;

        return (int) $cnpj[13] === $d2;
    }
}
