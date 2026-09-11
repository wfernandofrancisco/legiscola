<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aula de catálogo nascia sempre online, mesmo em turma presencial.
 *
 * Isso deixava o aluno de turma presencial confirmar a própria presença pela tela da aula.
 * A materialização já corrige as turmas novas; aqui acertamos as que foram abertas antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('class_lessons')
            ->whereNotNull('catalog_lesson_id')
            ->where('is_online', true)
            ->whereIn('course_class_id', fn ($query) => $query
                ->select('id')
                ->from('course_classes')
                ->where('tipo_turma', 'presencial')
            )
            ->update(['is_online' => false]);
    }

    public function down(): void
    {
        // Sem volta: não dá para separar o que era erro do que a câmara marcou de propósito.
    }
};
