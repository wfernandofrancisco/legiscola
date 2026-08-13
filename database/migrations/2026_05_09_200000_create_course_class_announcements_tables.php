<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('course_class_announcement_deliveries');
        Schema::dropIfExists('course_class_announcements');

        Schema::create('course_class_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants', 'id', 'cca_tenant_fk')->cascadeOnDelete();
            $table->foreignId('course_class_id')->constrained('course_classes', 'id', 'cca_class_fk')->cascadeOnDelete();
            $table->date('reference_date')->nullable();
            $table->string('subject', 190)->nullable();
            $table->text('body');
            $table->json('channels');
            $table->boolean('consent_acknowledged')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users', 'id', 'cca_user_fk')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['course_class_id', 'created_at'], 'cca_class_created_idx');
        });

        Schema::create('course_class_announcement_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants', 'id', 'ccad_tenant_fk')->cascadeOnDelete();
            $table->foreignId('course_class_announcement_id')
                ->constrained('course_class_announcements', 'id', 'ccad_ann_fk')
                ->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments', 'id', 'ccad_enroll_fk')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students', 'id', 'ccad_student_fk')->cascadeOnDelete();
            $table->string('channel', 16);
            $table->string('destination', 255)->nullable();
            $table->string('status', 24);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['course_class_announcement_id', 'channel'], 'ccad_ann_ch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_class_announcement_deliveries');
        Schema::dropIfExists('course_class_announcements');
    }
};
