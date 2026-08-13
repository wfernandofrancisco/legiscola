<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_responsible_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('cnpj', 14);
            $table->string('razao_social_informada')->nullable();
            $table->text('mensagem')->nullable();
            $table->string('status', 20)->default('pendente');
            $table->timestamps();

            $table->index(['tenant_id', 'cnpj', 'status']);
            $table->index(['tenant_id', 'user_id', 'status']);
        });

        if (Schema::hasTable('empresas')) {
            Schema::table('empresa_responsible_claims', function (Blueprint $table): void {
                $table->foreign('empresa_id')
                    ->references('id')
                    ->on('empresas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_responsible_claims');
    }
};
