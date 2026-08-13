<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professores_credenciamento_anexos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('professor_credenciamento_id')
                ->constrained('professores_credenciamentos', indexName: 'pc_anexos_cred_fk')
                ->cascadeOnDelete();
            $table->string('titulo');
            $table->string('arquivo_path');
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->index(['professor_credenciamento_id', 'ordem'], 'pc_anexos_ord_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professores_credenciamento_anexos');
    }
};
