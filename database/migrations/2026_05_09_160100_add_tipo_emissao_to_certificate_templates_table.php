<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('certificate_templates', 'tipo_emissao')) {
                $table->string('tipo_emissao', 20)->default('curso')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('certificate_templates', 'tipo_emissao')) {
                $table->dropColumn('tipo_emissao');
            }
        });
    }
};
