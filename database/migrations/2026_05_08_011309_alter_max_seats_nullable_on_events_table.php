<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE events MODIFY max_seats INT UNSIGNED NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('UPDATE events SET max_seats = 0 WHERE max_seats IS NULL');
        DB::statement('ALTER TABLE events MODIFY max_seats INT UNSIGNED NOT NULL DEFAULT 0');
    }
};
