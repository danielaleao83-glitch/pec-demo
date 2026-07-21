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

                if (! Schema::hasColumn('pacientes', 'cns')) {
                    $table->string('cns', 15)->nullable();
                    $table->index('cns', 'idx_pacientes_cns');
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

                if (Schema::hasColumn('pacientes', 'cns')) {
                    $table->dropIndex('idx_pacientes_cns');
                    $table->dropColumn('cns');
                }

            });

        }
    }
};
