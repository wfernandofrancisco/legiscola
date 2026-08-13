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
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('max_seats')->default(0);
            $table->dateTime('enrollment_start');
            $table->dateTime('enrollment_end');
            $table->enum('status', ['cadastrado', 'inscricao', 'em_andamento', 'concluido', 'cancelado'])->default('cadastrado');
            $table->timestamps();

            $table->index(['tenant_id', 'course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};
