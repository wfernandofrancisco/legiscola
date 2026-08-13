<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professores_credenciamentos', function (Blueprint $table): void {
            $table->unsignedSmallInteger('ano_referencia')->nullable()->after('titulo');
        });
    }

    public function down(): void
    {
        Schema::table('professores_credenciamentos', function (Blueprint $table): void {
            $table->dropColumn('ano_referencia');
        });
    }
};
