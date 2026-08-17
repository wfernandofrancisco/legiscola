<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('certificado_disponivel_ate')->nullable()->after('com_certificado');
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->dateTime('certificado_disponivel_ate')->nullable()->after('enrollment_end');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('certificado_disponivel_ate');
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn('certificado_disponivel_ate');
        });
    }
};
