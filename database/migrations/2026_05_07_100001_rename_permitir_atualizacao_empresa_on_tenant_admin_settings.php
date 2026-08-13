<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tenant_admin_settings', 'permitir_atualizacao_empresa_site')) {
            return;
        }

        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            $table->boolean('permitir_atualizacao_empresa')->default(false)->after('aprovar_alteracoes_empresas');
        });

        foreach (DB::table('tenant_admin_settings')->select('id', 'permitir_atualizacao_empresa_site')->get() as $row) {
            DB::table('tenant_admin_settings')->where('id', $row->id)->update([
                'permitir_atualizacao_empresa' => (bool) $row->permitir_atualizacao_empresa_site,
            ]);
        }

        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            $table->dropColumn('permitir_atualizacao_empresa_site');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tenant_admin_settings', 'permitir_atualizacao_empresa')) {
            return;
        }

        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            $table->boolean('permitir_atualizacao_empresa_site')->default(false)->after('aprovar_alteracoes_empresas');
        });

        foreach (DB::table('tenant_admin_settings')->select('id', 'permitir_atualizacao_empresa')->get() as $row) {
            DB::table('tenant_admin_settings')->where('id', $row->id)->update([
                'permitir_atualizacao_empresa_site' => (bool) $row->permitir_atualizacao_empresa,
            ]);
        }

        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            $table->dropColumn('permitir_atualizacao_empresa');
        });
    }
};
