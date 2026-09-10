<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Abrangência do diretor regional: quais UFs ele responde.
     *
     * Os tenants dele são derivados por tenants.estado — sem vínculo manual por cliente,
     * então um cliente novo cadastrado na UF já aparece para o diretor.
     */
    public function up(): void
    {
        Schema::create('director_ufs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('uf', 2);
            $table->timestamps();

            $table->unique(['user_id', 'uf'], 'uniq_director_ufs_user_uf');
            $table->index('uf', 'idx_director_ufs_uf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_ufs');
    }
};
