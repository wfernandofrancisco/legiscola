<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['tenant_id', 'course_id', 'name']);
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('max_seats')->default(0);
            $table->date('enrollment_start');
            $table->date('enrollment_end');
            $table->string('status', 20)->default('aberta');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('status', 20)->default('inscrito');
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'class_id'], 'uniq_enrollments_student_class');
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_online')->default(false);
            $table->string('meeting_url')->nullable();
            $table->string('material_url')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('class_schedule_id')->nullable()->after('tenant_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->boolean('is_present')->nullable()->after('status');
            $table->index(['tenant_id', 'class_schedule_id']);
            $table->unique(['tenant_id', 'class_schedule_id', 'student_id'], 'uniq_attendance_schedule_student');
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->longText('content');
            $table->string('type', 30);
            $table->timestamps();
        });

        Schema::create('exam_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('title');
            $table->longText('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_template_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('exam_template_id')->constrained('exam_templates')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['exam_template_id', 'question_id']);
        });

        Schema::create('exam_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('exam_template_id')->constrained('exam_templates')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attachments');
        Schema::dropIfExists('exam_template_question');
        Schema::dropIfExists('exam_templates');
        Schema::dropIfExists('questions');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('uniq_attendance_schedule_student');
            $table->dropIndex(['tenant_id', 'class_schedule_id']);
            $table->dropConstrainedForeignId('class_schedule_id');
            $table->dropColumn('is_present');
        });

        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('subjects');
    }
};
