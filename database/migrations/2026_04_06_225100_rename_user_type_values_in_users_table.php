<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Passo 1: expande o ENUM para ter os valores antigos + novos simultaneamente
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM(
            'funcionario','dono_empresa','cliente',
            'super_admin','tenant_admin','tenant_manager','tenant_user'
        ) NOT NULL DEFAULT 'tenant_user'");

        // Passo 2: migra os dados existentes
        DB::statement("UPDATE users SET user_type = 'super_admin'   WHERE user_type = 'funcionario'");
        DB::statement("UPDATE users SET user_type = 'tenant_admin'  WHERE user_type = 'dono_empresa'");
        DB::statement("UPDATE users SET user_type = 'tenant_user'   WHERE user_type = 'cliente'");

        // Passo 3: remove os valores antigos do ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM(
            'super_admin','tenant_admin','tenant_manager','tenant_user'
        ) NOT NULL DEFAULT 'tenant_user'");
    }

    public function down(): void
    {
        // Expande ENUM para transição reversa
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM(
            'super_admin','tenant_admin','tenant_manager','tenant_user',
            'funcionario','dono_empresa','cliente'
        ) NOT NULL DEFAULT 'cliente'");

        DB::statement("UPDATE users SET user_type = 'funcionario'  WHERE user_type = 'super_admin'");
        DB::statement("UPDATE users SET user_type = 'dono_empresa' WHERE user_type = 'tenant_admin'");
        DB::statement("UPDATE users SET user_type = 'cliente'      WHERE user_type IN ('tenant_user','tenant_manager')");

        // Restaura ENUM original
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM(
            'funcionario','dono_empresa','cliente'
        ) NOT NULL DEFAULT 'cliente'");
    }
};
