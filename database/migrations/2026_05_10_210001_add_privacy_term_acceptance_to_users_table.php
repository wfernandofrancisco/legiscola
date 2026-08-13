<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('accepted_global_privacy_term_version')->nullable()->after('email_verified_at');
            $table->timestamp('accepted_global_privacy_term_at')->nullable()->after('accepted_global_privacy_term_version');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['accepted_global_privacy_term_version', 'accepted_global_privacy_term_at']);
        });
    }
};
