<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Liga o conteúdo do cliente ao catálogo do diretor (modelo híbrido).
     *
     * A estrutura (curso, turmas, aulas) é criada dentro do tenant e é dele — datas, presença e
     * matrículas continuam locais. Já o vídeo e o material seguem morando no catálogo: a aula do
     * cliente aponta para a aula de origem via catalog_lesson_id e lê o conteúdo de lá.
     *
     * Colunas nulas = conteúdo próprio da câmara, que continua funcionando como antes.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('catalog_item_id')->nullable()->after('admin_user_id')
                ->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('catalog_license_id')->nullable()->after('catalog_item_id')
                ->constrained('catalog_licenses')->nullOnDelete();
        });

        Schema::table('class_lessons', function (Blueprint $table) {
            $table->foreignId('catalog_lesson_id')->nullable()->after('course_class_id')
                ->constrained('catalog_lessons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_lesson_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_license_id');
            $table->dropConstrainedForeignId('catalog_item_id');
        });
    }
};
