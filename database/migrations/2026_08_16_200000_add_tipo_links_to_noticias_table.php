<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->string('tipo', 20)->default('completa')->after('slug');
            $table->string('fonte_url', 2048)->nullable()->after('noticia');
            $table->string('video_url', 2048)->nullable()->after('fonte_url');

            $table->index(['tenant_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'tipo']);
            $table->dropColumn(['tipo', 'fonte_url', 'video_url']);
        });
    }
};
