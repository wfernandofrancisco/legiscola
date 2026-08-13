<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_lessons', function (Blueprint $table): void {
            $table->string('material_file_path')->nullable()->after('material_url');
            $table->string('material_file_name')->nullable()->after('material_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('class_lessons', function (Blueprint $table): void {
            $table->dropColumn(['material_file_path', 'material_file_name']);
        });
    }
};
