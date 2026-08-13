<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticia_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('noticia_id')->constrained('noticias')->onDelete('cascade');
            $table->string('path');
            $table->string('legenda')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'noticia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticia_fotos');
    }
};
