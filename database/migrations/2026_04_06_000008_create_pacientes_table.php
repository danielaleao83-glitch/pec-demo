<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(DB::raw('uuid_generate_v4()'));
            $table->string('nome');
            $table->text('cpf')->nullable();
            $table->string('cpf_hash', 64)->nullable();
            $table->text('cns')->nullable();
            $table->string('cns_hash', 64)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('sexo', 10)->nullable();
            $table->text('telefone')->nullable();
            $table->text('email')->nullable();
            $table->text('endereco')->nullable();
            $table->string('nome_mae')->nullable();
            $table->unsignedBigInteger('municipio')->nullable();
            $table->boolean('prioridade')->default(false);
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
