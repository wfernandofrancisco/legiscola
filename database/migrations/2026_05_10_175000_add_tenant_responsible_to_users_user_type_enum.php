<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM(
            'super_admin','tenant_admin','tenant_manager','tenant_user','tenant_responsible'
        ) NOT NULL DEFAULT 'tenant_user'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET user_type = 'tenant_user' WHERE user_type = 'tenant_responsible'");

        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM(
            'super_admin','tenant_admin','tenant_manager','tenant_user'
        ) NOT NULL DEFAULT 'tenant_user'");
    }
};
