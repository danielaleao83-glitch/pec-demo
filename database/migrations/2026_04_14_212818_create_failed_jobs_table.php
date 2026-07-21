<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {

            // 🆔 ID principal
            $table->bigIncrements('id');

            // 🌐 conexão da fila (redis, database, etc)
            $table->string('connection')->nullable();

            // 📦 nome da fila
            $table->string('queue')->index();

            // 🧠 payload do job (serializado Laravel)
            $table->longText('payload');

            // ❌ exceção detalhada
            $table->longText('exception');

            // ⏱ data da falha
            $table->timestamp('failed_at')->useCurrent()->index();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
