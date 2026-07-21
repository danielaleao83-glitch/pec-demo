<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->uuid('registro_id_uuid')
                  ->nullable()
                  ->index();
        });
    }

    public function down(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropColumn('registro_id_uuid');
        });
    }
};