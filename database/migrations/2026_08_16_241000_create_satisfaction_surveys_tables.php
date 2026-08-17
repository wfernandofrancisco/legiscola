<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Limpa restos de tentativa anterior (FK com nome longo no MySQL).
        Schema::dropIfExists('satisfaction_survey_answers');
        Schema::dropIfExists('satisfaction_survey_responses');
        Schema::dropIfExists('satisfaction_survey_options');
        Schema::dropIfExists('satisfaction_survey_questions');

        if (Schema::hasColumn('course_classes', 'satisfaction_survey_id')) {
            Schema::table('course_classes', function (Blueprint $table) {
                try {
                    $table->dropForeign('course_classes_ss_survey_fk');
                } catch (Throwable) {
                    // ignore
                }
                try {
                    $table->dropConstrainedForeignId('satisfaction_survey_id');
                } catch (Throwable) {
                    if (Schema::hasColumn('course_classes', 'satisfaction_survey_id')) {
                        $table->dropColumn('satisfaction_survey_id');
                    }
                }
                if (Schema::hasColumn('course_classes', 'satisfaction_survey_required')) {
                    $table->dropColumn('satisfaction_survey_required');
                }
            });
        }

        Schema::dropIfExists('satisfaction_surveys');

        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('satisfaction_survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('satisfaction_survey_id');
            $table->string('question');
            $table->string('tipo', 20);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('satisfaction_survey_id', 'ss_questions_survey_fk')
                ->references('id')->on('satisfaction_surveys')->cascadeOnDelete();
            $table->index(['satisfaction_survey_id', 'position'], 'ss_questions_survey_pos_idx');
        });

        Schema::create('satisfaction_survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('satisfaction_survey_question_id');
            $table->string('label');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('satisfaction_survey_question_id', 'ss_options_question_fk')
                ->references('id')->on('satisfaction_survey_questions')->cascadeOnDelete();
            $table->index(['satisfaction_survey_question_id', 'position'], 'ss_options_question_pos_idx');
        });

        Schema::create('satisfaction_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('satisfaction_survey_id');
            $table->foreignId('course_class_id')->constrained('course_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->foreign('satisfaction_survey_id', 'ss_responses_survey_fk')
                ->references('id')->on('satisfaction_surveys')->cascadeOnDelete();
            $table->unique(
                ['satisfaction_survey_id', 'course_class_id', 'student_id'],
                'ss_responses_unique_student'
            );
        });

        Schema::create('satisfaction_survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('satisfaction_survey_response_id');
            $table->unsignedBigInteger('satisfaction_survey_question_id');
            $table->unsignedBigInteger('satisfaction_survey_option_id')->nullable();
            $table->text('free_text')->nullable();
            $table->timestamps();

            $table->foreign('satisfaction_survey_response_id', 'ss_answers_response_fk')
                ->references('id')->on('satisfaction_survey_responses')->cascadeOnDelete();
            $table->foreign('satisfaction_survey_question_id', 'ss_answers_question_fk')
                ->references('id')->on('satisfaction_survey_questions')->cascadeOnDelete();
            $table->foreign('satisfaction_survey_option_id', 'ss_answers_option_fk')
                ->references('id')->on('satisfaction_survey_options')->nullOnDelete();
            $table->unique(
                ['satisfaction_survey_response_id', 'satisfaction_survey_question_id'],
                'ss_answers_unique_question'
            );
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('satisfaction_survey_id')->nullable()->after('certificado_disponivel_ate');
            $table->boolean('satisfaction_survey_required')->default(false)->after('satisfaction_survey_id');

            $table->foreign('satisfaction_survey_id', 'course_classes_ss_survey_fk')
                ->references('id')->on('satisfaction_surveys')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropForeign('course_classes_ss_survey_fk');
            $table->dropColumn(['satisfaction_survey_id', 'satisfaction_survey_required']);
        });

        Schema::dropIfExists('satisfaction_survey_answers');
        Schema::dropIfExists('satisfaction_survey_responses');
        Schema::dropIfExists('satisfaction_survey_options');
        Schema::dropIfExists('satisfaction_survey_questions');
        Schema::dropIfExists('satisfaction_surveys');
    }
};
