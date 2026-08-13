<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->foreignId('class_lesson_id')->nullable()->after('tenant_id')->constrained('class_lessons')->cascadeOnDelete();
            $table->index(['tenant_id', 'class_lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'class_lesson_id']);
            $table->dropConstrainedForeignId('class_lesson_id');
        });
    }
};
