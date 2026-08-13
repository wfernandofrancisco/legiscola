<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_admin_settings', 'cidade')) {
                $table->string('cidade', 120)->nullable()->after('uf');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_admin_settings', 'cidade')) {
                $table->dropColumn('cidade');
            }
        });
    }
};
