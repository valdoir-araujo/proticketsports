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
        Schema::create('dados_bancarios', function (Blueprint $table) {
            $table->id();
            
            // ======================================================================
            // ⬇️ CORREÇÃO DEFINITIVA APLICADA AQUI ⬇️
            // A tabela agora está corretamente ligada ao EVENTO, e não à organização.
            // ======================================================================
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            
            $table->string('nome_beneficiario');
            $table->string('pix_chave_tipo')->nullable();
            $table->string('pix_chave')->nullable();
            $table->string('banco_nome')->nullable();
            $table->string('banco_agencia')->nullable();
            $table->string('banco_conta')->nullable();
            $table->string('banco_tipo_conta')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dados_bancarios');
    }
};

