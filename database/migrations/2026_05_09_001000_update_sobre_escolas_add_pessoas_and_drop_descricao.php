<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sobre_escolas', function (Blueprint $table): void {
            if (Schema::hasColumn('sobre_escolas', 'descricao')) {
                $table->dropColumn('descricao');
            }
        });

        Schema::create('sobre_escola_pessoas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sobre_escola_id')->constrained('sobre_escolas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('cargo');
            $table->string('foto_path')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->index(['sobre_escola_id', 'ordem'], 'se_pessoas_ord_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sobre_escola_pessoas');

        Schema::table('sobre_escolas', function (Blueprint $table): void {
            if (! Schema::hasColumn('sobre_escolas', 'descricao')) {
                $table->longText('descricao')->nullable()->after('quem_somos');
            }
        });
    }
};
