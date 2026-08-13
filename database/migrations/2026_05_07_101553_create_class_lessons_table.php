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
        if (Schema::hasTable('class_lessons')) {
            return;
        }

        Schema::create('class_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('course_class_id');
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_online')->default(false);
            $table->text('video_url')->nullable();
            $table->text('material_url')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'course_class_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('class_lessons')) {
            return;
        }

        Schema::dropIfExists('class_lessons');
    }
};
