<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_lessons', function (Blueprint $table) {
            $table->foreignId('course_lesson_id')
                ->nullable()
                ->after('catalog_lesson_id')
                ->constrained('course_lessons')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_lesson_id');
        });
    }
};
