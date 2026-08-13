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
        if (Schema::hasTable('quizzes')) {
            Schema::table('quizzes', function (Blueprint $table) {
                if (! Schema::hasColumn('quizzes', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('min_score_to_pass');
                }
            });
        }

        if (! Schema::hasTable('course_class_quiz')) {
            Schema::create('course_class_quiz', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('course_class_id')->constrained('course_classes')->cascadeOnDelete();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['course_class_id', 'quiz_id']);
            });
        }

        if (! Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->foreignId('course_class_id')->constrained('course_classes')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->decimal('score', 5, 2)->default(0);
                $table->unsignedInteger('correct_answers')->default(0);
                $table->unsignedInteger('total_questions')->default(0);
                $table->boolean('passed')->default(false);
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quiz_attempt_answers')) {
            Schema::create('quiz_attempt_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
                $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $table->foreignId('quiz_answer_id')->nullable()->constrained('quiz_answers')->nullOnDelete();
                $table->boolean('is_correct')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('course_class_quiz');

        if (Schema::hasTable('quizzes') && Schema::hasColumn('quizzes', 'is_active')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
