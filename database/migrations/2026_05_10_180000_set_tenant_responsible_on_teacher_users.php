<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Docentes cadastrados como professores passam a usar user_type tenant_responsible (rotas /docente).
     */
    public function up(): void
    {
        DB::table('users')
            ->join('teachers', 'teachers.user_id', '=', 'users.id')
            ->where('users.user_type', 'tenant_user')
            ->update(['users.user_type' => 'tenant_responsible']);
    }

    public function down(): void
    {
        DB::table('users')
            ->join('teachers', 'teachers.user_id', '=', 'users.id')
            ->where('users.user_type', 'tenant_responsible')
            ->update(['users.user_type' => 'tenant_user']);
    }
};
