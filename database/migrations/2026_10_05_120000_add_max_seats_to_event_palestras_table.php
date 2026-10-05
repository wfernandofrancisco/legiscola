<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_palestras', function (Blueprint $table): void {
            $table->unsignedInteger('max_seats')->nullable()->after('date_time');
        });

        foreach (DB::table('events')->whereNotNull('max_seats')->where('max_seats', '>', 0)->cursor() as $event) {
            DB::table('event_palestras')
                ->where('event_id', $event->id)
                ->whereNull('max_seats')
                ->update(['max_seats' => $event->max_seats]);
        }
    }

    public function down(): void
    {
        Schema::table('event_palestras', function (Blueprint $table): void {
            $table->dropColumn('max_seats');
        });
    }
};
