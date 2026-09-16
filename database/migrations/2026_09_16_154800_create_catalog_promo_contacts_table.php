<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_promo_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_promo_id')->constrained('catalog_promos')->cascadeOnDelete();
            $table->foreignId('director_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome');
            $table->string('whatsapp', 20);
            $table->text('interesse');
            $table->timestamps();

            $table->index(['director_user_id', 'created_at']);
            $table->index(['catalog_promo_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_promo_contacts');
    }
};
