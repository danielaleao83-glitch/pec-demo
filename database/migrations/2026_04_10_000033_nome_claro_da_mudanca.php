<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pacientes')) {

            Schema::table('pacientes', function (Blueprint $table) {

                if (! Schema::hasColumn('pacientes', 'telefone')) {
                    $table->string('telefone', 20)->nullable();
                    $table->index('telefone', 'idx_pacientes_telefone');
                }

                if (! Schema::hasColumn('pacientes', 'nome_mae')) {
                    $table->string('nome_mae')->nullable();
                }

            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pacientes')) {

            Schema::table('pacientes', function (Blueprint $table) {

                if (Schema::hasColumn('pacientes', 'telefone')) {
                    $table->dropIndex('idx_pacientes_telefone');
                    $table->dropColumn('telefone');
                }

                if (Schema::hasColumn('pacientes', 'nome_mae')) {
                    $table->dropColumn('nome_mae');
                }

            });

        }
    }
};
