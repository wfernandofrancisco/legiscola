<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_enrollments', function (Blueprint $table): void {
            if (! Schema::hasColumn('event_enrollments', 'presente')) {
                $table->boolean('presente')->default(false)->after('student_id');
            }
        });

        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'com_certificado')) {
                $table->boolean('com_certificado')->default(false)->after('allow_online_registration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            if (Schema::hasColumn('events', 'com_certificado')) {
                $table->dropColumn('com_certificado');
            }
        });

        Schema::table('event_enrollments', function (Blueprint $table): void {
            if (Schema::hasColumn('event_enrollments', 'presente')) {
                $table->dropColumn('presente');
            }
        });
    }
};
