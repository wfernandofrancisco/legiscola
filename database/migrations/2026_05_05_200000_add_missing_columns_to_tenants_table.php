<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('razao_social')->nullable()->after('description');
            $table->string('nome_fantasia')->nullable()->after('razao_social');
            $table->string('cnpj', 14)->nullable()->unique()->after('nome_fantasia');
            $table->string('contact_email')->nullable()->after('cnpj');
            $table->string('phone', 20)->nullable()->after('contact_email');
            $table->string('website')->nullable()->after('phone');
            $table->string('cep', 9)->nullable()->after('website');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('numero', 20)->nullable()->after('logradouro');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('bairro')->nullable()->after('complemento');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('estado', 2)->nullable()->after('cidade');
            $table->decimal('latitude', 10, 7)->nullable()->after('estado');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('codigo_ibge_municipio', 10)->nullable()->after('longitude');
            $table->text('observacoes')->nullable()->after('codigo_ibge_municipio');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'razao_social',
                'nome_fantasia',
                'cnpj',
                'contact_email',
                'phone',
                'website',
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'estado',
                'latitude',
                'longitude',
                'codigo_ibge_municipio',
                'observacoes',
            ]);
        });
    }
};
