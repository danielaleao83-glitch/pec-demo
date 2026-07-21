<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 🚀 APPLY
     */
    public function up(): void
    {
        Schema::table('blocked_access', function (Blueprint $table) {

            // 🔥 índice único (user + ip)
            $table->unique(['user_id', 'ip'], 'blocked_user_ip_unique');
        });
    }

    /**
     * 🔄 ROLLBACK
     */
    public function down(): void
    {
        Schema::table('blocked_access', function (Blueprint $table) {

            $table->dropUnique('blocked_user_ip_unique');
        });
    }
};