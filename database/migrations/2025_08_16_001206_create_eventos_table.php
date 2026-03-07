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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();

            // Chave Estrangeira (Foreign Key) para a tabela 'organizadores'
            // constrained() -> Garante a integridade: um evento não pode existir sem um organizador.
            // cascadeOnDelete() -> Robusto: Se um organizador for excluído, todos os seus eventos são excluídos automaticamente.
            $table->foreignId('organizador_id')->constrained('organizadores')->cascadeOnDelete();

            $table->string('nome');
            $table->string('slug')->unique()->comment('URL amigável para o evento, ex: corrida-de-montanha-2025');

            $table->text('descricao_curta')->nullable();
            $table->longText('descricao_completa')->nullable();

            $table->string('banner_url')->nullable();

            // Colunas de data/hora com índices para buscas rápidas
            $table->dateTime('data_evento')->index();
            $table->date('data_inicio_inscricoes')->index();
            $table->date('data_fim_inscricoes')->index();

            // Colunas de localização com índices para filtros geográficos
            $table->string('local', 255);
            $table->string('cidade', 100)->index();
            $table->string('estado', 2)->index(); // Sigla do estado, ex: PR, SC

            // Status do evento para controle e filtros
            $table->enum('status', ['rascunho', 'publicado', 'cancelado', 'realizado'])->default('rascunho')->index();

            $table->timestamps();
        });      
                
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
