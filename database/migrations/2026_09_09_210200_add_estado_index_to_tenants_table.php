<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A abrangência do diretor resolve "tenants where estado in (...)" em toda request da área /diretor.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->index('estado', 'idx_tenants_estado');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('idx_tenants_estado');
        });
    }
};
