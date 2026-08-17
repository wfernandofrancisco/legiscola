<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('palestrante_nome')->nullable()->after('presenca_fim_em');
            $table->string('palestrante_cpf', 11)->nullable()->after('palestrante_nome');
            $table->string('palestrante_senha')->nullable()->after('palestrante_cpf');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        DB::statement('ALTER TABLE certificates MODIFY student_id BIGINT UNSIGNED NULL');

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        DB::statement('UPDATE certificates SET student_id = (SELECT id FROM students ORDER BY id ASC LIMIT 1) WHERE student_id IS NULL');
        DB::statement('ALTER TABLE certificates MODIFY student_id BIGINT UNSIGNED NOT NULL');

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['palestrante_nome', 'palestrante_cpf', 'palestrante_senha']);
        });
    }
};
