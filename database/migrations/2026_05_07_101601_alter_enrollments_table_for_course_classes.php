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
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->foreignId('course_class_id')->nullable()->after('class_id')->constrained('course_classes')->cascadeOnDelete();
            $table->text('observations')->nullable()->after('status');
            $table->index(['tenant_id', 'course_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'course_class_id']);
            $table->dropConstrainedForeignId('course_class_id');
            $table->dropColumn('observations');
        });
    }
};
