<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_lessons', function (Blueprint $table): void {
            if (! Schema::hasColumn('class_lessons', 'video_path')) {
                $table->string('video_path')->nullable()->after('video_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_lessons', function (Blueprint $table): void {
            if (Schema::hasColumn('class_lessons', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });
    }
};
