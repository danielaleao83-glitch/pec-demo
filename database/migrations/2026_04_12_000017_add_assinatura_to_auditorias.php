<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 🔐 ASSINATURA DIGITAL
            |--------------------------------------------------------------------------
            | Suporta:
            | - HMAC
            | - JWS (gov)
            | - ICP-Brasil
            |--------------------------------------------------------------------------
            */
            if (! Schema::hasColumn('auditorias', 'assinatura')) {
                $table->text('assinatura')->nullable();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 🔍 ÍNDICE (PERFORMANCE FORENSE)
        |--------------------------------------------------------------------------
        */
        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_auditorias_assinatura
            ON auditorias (assinatura);
        ');
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 🔄 ROLLBACK SEGURO
        |--------------------------------------------------------------------------
        */
        DB::statement('
            DROP INDEX IF EXISTS idx_auditorias_assinatura;
        ');

        Schema::table('auditorias', function (Blueprint $table) {

            if (Schema::hasColumn('auditorias', 'assinatura')) {
                $table->dropColumn('assinatura');
            }
        });
    }
};
