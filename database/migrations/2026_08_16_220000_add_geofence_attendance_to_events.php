<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('chamada_georreferencia')->default(false)->after('certificado_disponivel_ate');
            $table->decimal('latitude', 10, 7)->nullable()->after('chamada_georreferencia');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('geofence_raio_metros')->nullable()->after('longitude');
            $table->dateTime('presenca_inicio_em')->nullable()->after('geofence_raio_metros');
            $table->dateTime('presenca_fim_em')->nullable()->after('presenca_inicio_em');
        });

        Schema::table('event_enrollments', function (Blueprint $table) {
            $table->decimal('checkin_latitude', 10, 7)->nullable()->after('presente');
            $table->decimal('checkin_longitude', 10, 7)->nullable()->after('checkin_latitude');
            $table->unsignedInteger('checkin_accuracy_metros')->nullable()->after('checkin_longitude');
            $table->timestamp('checkin_em')->nullable()->after('checkin_accuracy_metros');
        });
    }

    public function down(): void
    {
        Schema::table('event_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_latitude',
                'checkin_longitude',
                'checkin_accuracy_metros',
                'checkin_em',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'chamada_georreferencia',
                'latitude',
                'longitude',
                'geofence_raio_metros',
                'presenca_inicio_em',
                'presenca_fim_em',
            ]);
        });
    }
};
