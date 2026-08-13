<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sobre_escolas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->longText('institucional')->nullable();
            $table->longText('objetivos')->nullable();
            $table->longText('quem_somos')->nullable();
            $table->longText('descricao')->nullable();
            $table->longText('projeto_pedagogico')->nullable();
            $table->longText('legislacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sobre_escolas');
    }
};
