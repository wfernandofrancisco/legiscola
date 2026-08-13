<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Permite várias presenças por aluno/curso no mesmo dia (aulas online distintas)
     * e garante no máximo um registro por aluno por class_lesson_id quando preenchido.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            if (! $this->indexExists('attendances_student_id_index')) {
                $table->index('student_id', 'attendances_student_id_index');
            }
            if (! $this->indexExists('attendances_course_id_index')) {
                $table->index('course_id', 'attendances_course_id_index');
            }
        });

        Schema::table('attendances', function (Blueprint $table): void {
            if ($this->indexExists('uniq_attendance')) {
                $table->dropUnique('uniq_attendance');
            }
        });

        Schema::table('attendances', function (Blueprint $table): void {
            if (! $this->indexExists('uniq_attendance_tenant_student_lesson')) {
                $table->unique(
                    ['tenant_id', 'student_id', 'class_lesson_id'],
                    'uniq_attendance_tenant_student_lesson'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            if ($this->indexExists('uniq_attendance_tenant_student_lesson')) {
                $table->dropUnique('uniq_attendance_tenant_student_lesson');
            }
        });

        Schema::table('attendances', function (Blueprint $table): void {
            if (! $this->indexExists('uniq_attendance')) {
                $table->unique(
                    ['student_id', 'course_id', 'class_date', 'curriculum_id'],
                    'uniq_attendance'
                );
            }
        });

        Schema::table('attendances', function (Blueprint $table): void {
            if ($this->indexExists('attendances_student_id_index')) {
                $table->dropIndex('attendances_student_id_index');
            }
            if ($this->indexExists('attendances_course_id_index')) {
                $table->dropIndex('attendances_course_id_index');
            }
        });
    }

    private function indexExists(string $name): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $database = $connection->getDatabaseName();

        if ($driver === 'mysql') {
            $row = $connection->selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, 'attendances', $name]
            );

            return isset($row->c) && (int) $row->c > 0;
        }

        return Schema::hasIndex('attendances', $name);
    }
};
