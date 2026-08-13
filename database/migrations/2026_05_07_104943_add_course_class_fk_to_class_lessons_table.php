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
        Schema::table('class_lessons', function (Blueprint $table) {
            $table->foreign('course_class_id')
                ->references('id')
                ->on('course_classes')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_lessons', function (Blueprint $table) {
            $table->dropForeign(['course_class_id']);
        });
    }
};
