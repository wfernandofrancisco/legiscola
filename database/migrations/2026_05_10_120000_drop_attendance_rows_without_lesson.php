<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Presença passa a existir apenas por aula (class_lesson_id obrigatório no uso atual).
     */
    public function up(): void
    {
        DB::table('attendances')->whereNull('class_lesson_id')->delete();
    }

    public function down(): void
    {
        // Irreversível: dados antigos da ficha por data foram removidos de propósito.
    }
};
