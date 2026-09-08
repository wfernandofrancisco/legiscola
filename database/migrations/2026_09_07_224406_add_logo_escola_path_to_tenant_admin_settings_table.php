<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Emblema da Escola Legislativa (portal — cabeçalho ao lado do emblema da câmara).
     */
    public function up(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_admin_settings', 'logo_escola_path')) {
                $table->string('logo_escola_path', 512)->nullable()->after('logo_prefeitura_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_admin_settings', 'logo_escola_path')) {
                $table->dropColumn('logo_escola_path');
            }
        });
    }
};
