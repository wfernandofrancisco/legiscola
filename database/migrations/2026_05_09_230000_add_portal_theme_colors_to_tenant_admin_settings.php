<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_admin_settings', 'primary_color')) {
                $table->string('primary_color', 16)->nullable()->after('logo_prefeitura_path');
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'secondary_color')) {
                $table->string('secondary_color', 16)->nullable()->after('primary_color');
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'tertiary_color')) {
                $table->string('tertiary_color', 16)->nullable()->after('secondary_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            foreach (['primary_color', 'secondary_color', 'tertiary_color'] as $col) {
                if (Schema::hasColumn('tenant_admin_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
