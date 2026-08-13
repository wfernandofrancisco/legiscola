<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            $table->string('nome_secretaria', 255)->nullable();
            $table->string('logo_prefeitura_path', 500)->nullable();
            $table->string('logo_secretaria_path', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            $table->dropColumn(['nome_secretaria', 'logo_prefeitura_path', 'logo_secretaria_path']);
        });
    }
};
