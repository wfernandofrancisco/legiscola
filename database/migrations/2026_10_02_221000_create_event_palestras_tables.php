<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_palestras', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem')->default(1);
            $table->string('title');
            $table->dateTime('date_time');
            $table->boolean('com_certificado')->default(false);
            $table->string('palestrante_nome')->nullable();
            $table->string('palestrante_cpf', 11)->nullable();
            $table->string('palestrante_senha')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'ordem']);
            $table->index(['tenant_id', 'date_time']);
        });

        Schema::create('event_enrollment_palestras', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_enrollment_id')->constrained('event_enrollments')->cascadeOnDelete();
            $table->foreignId('event_palestra_id')->constrained('event_palestras')->cascadeOnDelete();
            $table->boolean('presente')->default(false);
            $table->timestamps();

            $table->unique(['event_enrollment_id', 'event_palestra_id'], 'event_enroll_palestra_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_enrollment_palestras');
        Schema::dropIfExists('event_palestras');
    }
};
