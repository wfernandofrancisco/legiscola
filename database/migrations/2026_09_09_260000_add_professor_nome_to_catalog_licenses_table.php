<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quem ministra o conteúdo liberado — entra no certificado do aluno/participante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_licenses', function (Blueprint $table) {
            $table->string('professor_nome')->nullable()->after('max_inscritos');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_licenses', function (Blueprint $table) {
            $table->dropColumn('professor_nome');
        });
    }
};
