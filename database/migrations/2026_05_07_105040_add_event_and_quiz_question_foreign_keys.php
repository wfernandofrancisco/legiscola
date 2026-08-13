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
        Schema::table('event_enrollments', function (Blueprint $table) {
            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->foreign('quiz_id')
                ->references('id')
                ->on('quizzes')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });

        Schema::table('event_enrollments', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
    }
};
