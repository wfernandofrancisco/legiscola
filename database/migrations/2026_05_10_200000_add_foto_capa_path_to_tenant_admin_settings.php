<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Imagem de fundo do hero da home do portal público (subdomínio do tenant).
     */
    public function up(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_admin_settings', 'foto_capa_path')) {
                $table->string('foto_capa_path', 512)->nullable()->after('logo_prefeitura_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_admin_settings', 'foto_capa_path')) {
                $table->dropColumn('foto_capa_path');
            }
        });
    }
};
