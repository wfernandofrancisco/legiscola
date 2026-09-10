<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Liberação de um item do catálogo para uma câmara — o contrato entre diretor e cliente.
     *
     * Guarda tanto as regras de uso (até quando exibir, quantas turmas) quanto o controle
     * financeiro básico (valor, pagamento, nota fiscal). Uma licença por item/cliente:
     * renovação é edição da mesma linha, não uma nova.
     */
    public function up(): void
    {
        Schema::create('catalog_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_item_id')->constrained('catalog_items')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('director_user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['rascunho', 'ativa', 'suspensa', 'expirada', 'cancelada'])->default('rascunho');
            $table->timestamp('liberado_em')->nullable();
            $table->date('exibir_ate')->nullable();
            $table->unsignedInteger('max_turmas')->nullable();
            $table->text('observacoes')->nullable();

            $table->decimal('valor', 10, 2)->nullable();
            $table->enum('pagamento_status', ['pendente', 'parcial', 'pago', 'isento', 'cancelado'])->default('pendente');
            $table->date('vencimento_em')->nullable();
            $table->date('pago_em')->nullable();
            $table->string('forma_pagamento')->nullable();

            $table->string('nota_fiscal_numero')->nullable();
            $table->date('nota_fiscal_emitida_em')->nullable();
            $table->string('nota_fiscal_arquivo_path')->nullable();

            $table->timestamps();

            $table->unique(['catalog_item_id', 'tenant_id'], 'uniq_catalog_licenses_item_tenant');
            $table->index(['tenant_id', 'status'], 'idx_catalog_licenses_tenant_status');
            $table->index(['director_user_id', 'pagamento_status'], 'idx_catalog_licenses_director_pagamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_licenses');
    }
};
