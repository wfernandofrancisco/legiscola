<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            if (! Schema::hasColumn('certificates', 'event_id')) {
                $table->foreignId('event_id')->nullable()->after('course_id')->constrained('events')->nullOnDelete();
            }
        });

        Schema::disableForeignKeyConstraints();

        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropForeign(['course_id']);
        });

        DB::statement('ALTER TABLE certificates MODIFY course_id BIGINT UNSIGNED NULL');

        Schema::table('certificates', function (Blueprint $table): void {
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            if (Schema::hasColumn('certificates', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }
        });

        Schema::disableForeignKeyConstraints();

        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropForeign(['course_id']);
        });

        DB::statement('UPDATE certificates SET course_id = (SELECT id FROM courses ORDER BY id ASC LIMIT 1) WHERE course_id IS NULL');
        DB::statement('ALTER TABLE certificates MODIFY course_id BIGINT UNSIGNED NOT NULL');

        Schema::table('certificates', function (Blueprint $table): void {
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }
};
