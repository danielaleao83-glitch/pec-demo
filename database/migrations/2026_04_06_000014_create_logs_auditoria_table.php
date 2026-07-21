<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('acao');
            $table->text('detalhes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('acao');
            $table->index('created_at');
            $table->index(['acao', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
