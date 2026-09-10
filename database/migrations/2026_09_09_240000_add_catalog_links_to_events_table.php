<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Palestra do catálogo regional materializada como evento da câmara.
 *
 * O evento é da câmara (inscrições, presença, certificado são locais); as colunas abaixo só
 * dizem de onde ele veio, para o limite da licença e para a origem aparecer na tela.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('catalog_item_id')->nullable()->after('tenant_id')
                ->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('catalog_license_id')->nullable()->after('catalog_item_id')
                ->constrained('catalog_licenses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_license_id');
            $table->dropConstrainedForeignId('catalog_item_id');
        });
    }
};
