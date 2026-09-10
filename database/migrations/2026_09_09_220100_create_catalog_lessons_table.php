<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aulas do item de catálogo.
     *
     * `video_path` e o provedor `upload` já nascem aqui para o upload direto de vídeo entrar
     * depois sem nova migration — no v1 só o link é usado.
     */
    public function up(): void
    {
        Schema::create('catalog_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_item_id')->constrained('catalog_items')->cascadeOnDelete();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('video_provider', ['youtube', 'vimeo', 'externo', 'upload'])->nullable();
            $table->text('video_url')->nullable();
            $table->string('video_path')->nullable();
            $table->unsignedInteger('video_duracao_segundos')->nullable();
            $table->text('material_url')->nullable();
            $table->string('material_file_path')->nullable();
            $table->string('material_file_name')->nullable();
            $table->timestamps();

            $table->index(['catalog_item_id', 'ordem'], 'idx_catalog_lessons_item_ordem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_lessons');
    }
};
