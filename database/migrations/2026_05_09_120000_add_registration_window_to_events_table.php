<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'registration_starts_at')) {
                $table->dateTime('registration_starts_at')->nullable()->after('allow_online_registration');
            }
            if (! Schema::hasColumn('events', 'registration_ends_at')) {
                $table->dateTime('registration_ends_at')->nullable()->after('registration_starts_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            if (Schema::hasColumn('events', 'registration_ends_at')) {
                $table->dropColumn('registration_ends_at');
            }
            if (Schema::hasColumn('events', 'registration_starts_at')) {
                $table->dropColumn('registration_starts_at');
            }
        });
    }
};
