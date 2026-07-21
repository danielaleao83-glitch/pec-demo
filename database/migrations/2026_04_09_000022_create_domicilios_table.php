<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domicilios', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
            |-------------------------------------------------------------
            | 🧠 IDENTIFICAÇÃO SUS
            |-------------------------------------------------------------
            */
            $table->string('cns_responsavel')->nullable(); // CNS do responsável familiar
            $table->string('cpf_responsavel')->nullable();

            /*
            |-------------------------------------------------------------
            | 🏠 ENDEREÇO COMPLETO (PADRÃO e-SUS APS)
            |-------------------------------------------------------------
            */
            $table->string('logradouro');
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cep')->nullable();
            $table->string('municipio');
            $table->string('uf', 2);

            /*
            |-------------------------------------------------------------
            | 🏘️ CLASSIFICAÇÃO DO DOMICÍLIO (SUS / ESF)
            |-------------------------------------------------------------
            */
            $table->integer('tipo_moradia');
            // 1 = casa
            // 2 = apartamento
            // 3 = barraco
            // 4 = instituição
            // 5 = outro

            $table->integer('condicao_moradia')->nullable();
            // 1 = adequada
            // 2 = inadequada
            // 3 = área de risco

            $table->integer('situacao_imovel')->nullable();
            // 1 = próprio
            // 2 = alugado
            // 3 = cedido
            // 4 = ocupação

            /*
            |-------------------------------------------------------------
            | 🧑‍⚕️ TERRITORIALIZAÇÃO ESF / ACS
            |-------------------------------------------------------------
            */
            $table->string('microarea')->nullable(); // ACS
            $table->string('equipe_esf')->nullable(); // equipe ESF
            $table->string('cnes_equipe')->nullable(); // vínculo CNES

            /*
            |-------------------------------------------------------------
            | 📊 CONTROLE SUS / GESTÃO
            |-------------------------------------------------------------
            */
            $table->boolean('visitado_acs')->default(false);
            $table->timestamp('ultima_visita_acs')->nullable();

            $table->boolean('ativo')->default(true);

            /*
            |-------------------------------------------------------------
            | 🔐 AUDITORIA
            |-------------------------------------------------------------
            */
            $table->timestamps();
            $table->softDeletes();

            /*
            |-------------------------------------------------------------
            | INDEXAÇÃO (IMPORTANTE PARA PRODUÇÃO SUS)
            |-------------------------------------------------------------
            */
            $table->index('microarea');
            $table->index('equipe_esf');
            $table->index('municipio');
            $table->index('cns_responsavel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domicilios');
    }
};
