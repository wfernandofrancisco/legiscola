<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_class_quiz', function (Blueprint $table): void {
            if (! Schema::hasColumn('course_class_quiz', 'opens_at')) {
                $table->timestamp('opens_at')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('course_class_quiz', 'closes_at')) {
                $table->timestamp('closes_at')->nullable()->after('opens_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_class_quiz', function (Blueprint $table): void {
            if (Schema::hasColumn('course_class_quiz', 'closes_at')) {
                $table->dropColumn('closes_at');
            }
            if (Schema::hasColumn('course_class_quiz', 'opens_at')) {
                $table->dropColumn('opens_at');
            }
        });
    }
};
