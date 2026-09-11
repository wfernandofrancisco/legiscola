<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avisos/promoções do diretor regional → dashboard do admin da câmara.
 *
 * Alcance geral = todas as câmaras ativas das UFs do diretor (resolvido na hora da exibição).
 * Alcance específico = pivot catalog_promo_tenant.
 *
 * O dismiss do admin guarda o updated_at do aviso: não volta todo dia; só se o diretor editar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('director_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('catalog_item_id')->constrained('catalog_items')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('mensagem')->nullable();
            $table->decimal('preco_de', 10, 2)->nullable();
            $table->decimal('preco_por', 10, 2)->nullable();
            $table->unsignedTinyInteger('desconto_percentual')->nullable();
            $table->string('alcance', 20)->default('geral'); // geral | especifico
            $table->date('inicia_em')->nullable();
            $table->date('termina_em')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['director_user_id', 'ativo']);
            $table->index(['inicia_em', 'termina_em']);
        });

        Schema::create('catalog_promo_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_promo_id')->constrained('catalog_promos')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['catalog_promo_id', 'tenant_id']);
        });

        Schema::create('catalog_promo_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_promo_id')->constrained('catalog_promos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Cópia do updated_at do aviso no momento do fechamento.
            $table->timestamp('promo_updated_at');
            $table->timestamp('dismissed_at')->useCurrent();

            $table->unique(['catalog_promo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_promo_dismissals');
        Schema::dropIfExists('catalog_promo_tenant');
        Schema::dropIfExists('catalog_promos');
    }
};
