<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aulas de conteúdo do curso da câmara (sem data/hora).
     *
     * A agenda fica na grade da turma (`class_lessons`).
     */
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('video_url')->nullable();
            $table->string('video_path')->nullable();
            $table->text('material_url')->nullable();
            $table->string('material_file_path')->nullable();
            $table->string('material_file_name')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'ordem'], 'idx_course_lessons_course_ordem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};
