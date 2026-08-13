<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->after('user_id');
            $table->string('sexo', 20)->nullable()->after('birth_date');
            $table->string('status', 20)->default('ativo')->after('photo_path');
            $table->string('telefone', 20)->nullable()->after('cpf');
            $table->string('celular', 20)->nullable()->after('telefone');
            $table->string('cep', 9)->nullable()->after('celular');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('bairro')->nullable()->after('logradouro');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');

            $table->unique('email');
            $table->unique('cpf');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['cpf']);

            $table->dropColumn([
                'email',
                'sexo',
                'status',
                'telefone',
                'celular',
                'cep',
                'logradouro',
                'bairro',
                'cidade',
                'uf',
            ]);
        });
    }
};
