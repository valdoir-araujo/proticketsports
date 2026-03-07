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
        Schema::create('organizadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Informações de Contato e Identificação
            $table->string('nome_fantasia');
            $table->string('telefone', 20);

            // ==========================================================
            // NOVOS CAMPOS DE ENDEREÇO ADICIONADOS AQUI
            // ==========================================================
            $table->string('cep', 9)->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->foreignId('cidade_id')->nullable()->constrained('cidades');
            $table->foreignId('estado_id')->nullable()->constrained('estados');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizadores');
    }
};