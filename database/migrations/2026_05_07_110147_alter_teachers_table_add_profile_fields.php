<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('full_name')->nullable()->after('user_id');
            $table->string('email')->nullable()->after('full_name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('photo_path')->nullable()->after('phone');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo')->after('photo_path');
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'email']);
            $table->dropColumn(['full_name', 'email', 'phone', 'photo_path', 'status']);
        });
    }
};
