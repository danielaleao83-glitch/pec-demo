<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos_sistema', function (Blueprint $table) {

            if (! Schema::hasColumn('eventos_sistema', 'hash_anterior')) {
                $table->string('hash_anterior', 64)->nullable();
            }

            if (! Schema::hasColumn('eventos_sistema', 'hash_atual')) {
                $table->string('hash_atual', 64)->nullable();
            }

        });
    }

    public function down(): void
    {
        Schema::table('eventos_sistema', function (Blueprint $table) {

            if (Schema::hasColumn('eventos_sistema', 'hash_anterior')) {
                $table->dropColumn('hash_anterior');
            }

            if (Schema::hasColumn('eventos_sistema', 'hash_atual')) {
                $table->dropColumn('hash_atual');
            }

        });
    }
};
