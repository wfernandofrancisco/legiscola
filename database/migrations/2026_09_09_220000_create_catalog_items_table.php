<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo do diretor regional: cursos e palestras que ele produz e licencia.
     *
     * Primeira entidade de conteúdo sem tenant_id — pertence ao diretor (owner_user_id) e só
     * vira conteúdo de um cliente quando a licença é ativada. Por isso não usa BelongsToTenant.
     */
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['curso', 'palestra'])->default('curso');
            $table->string('titulo');
            $table->string('resumo')->nullable();
            $table->text('descricao')->nullable();
            $table->unsignedInteger('workload_hours')->nullable();
            $table->string('capa_path')->nullable();
            $table->enum('status', ['rascunho', 'publicado', 'arquivado'])->default('rascunho');
            $table->decimal('preco_sugerido', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_user_id', 'status'], 'idx_catalog_items_owner_status');
            $table->index('tipo', 'idx_catalog_items_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
