<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('cpf', 14)->nullable()->unique()->after('phone');
            $table->enum('user_type', ['funcionario', 'dono_empresa', 'cliente'])
                  ->default('cliente')
                  ->after('cpf');
            $table->string('avatar')->nullable()->after('user_type');
            $table->enum('status', ['ativo', 'inativo', 'pendente'])
                  ->default('pendente')
                  ->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'cpf', 'user_type', 'avatar',
                'status', 'last_login_at', 'last_login_ip', 'deleted_at',
            ]);
        });
    }
};
