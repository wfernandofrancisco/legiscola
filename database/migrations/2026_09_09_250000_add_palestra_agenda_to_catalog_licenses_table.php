<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dados de agenda da palestra na licença.
 *
 * A câmara ainda materializa o Event, mas o diretor já registra modalidade, data sugerida
 * e teto de inscritos — o que alimenta o calendário regional e pré-preenche o formulário
 * da câmara.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_licenses', function (Blueprint $table) {
            $table->string('modalidade', 20)->nullable()->after('max_turmas');
            $table->dateTime('palestra_em')->nullable()->after('modalidade');
            $table->unsignedInteger('max_inscritos')->nullable()->after('palestra_em');

            $table->index(['director_user_id', 'palestra_em'], 'idx_catalog_licenses_director_palestra');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_licenses', function (Blueprint $table) {
            $table->dropIndex('idx_catalog_licenses_director_palestra');
            $table->dropColumn(['modalidade', 'palestra_em', 'max_inscritos']);
        });
    }
};
