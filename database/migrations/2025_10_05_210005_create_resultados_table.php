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
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscricao_id')->unique()->constrained()->onDelete('cascade');
            // Armazena o tempo como um inteiro de milissegundos para máxima precisão
            $table->unsignedBigInteger('tempo_em_ms')->nullable();
            $table->unsignedInteger('posicao_categoria')->nullable();
            // Coluna com o nome correto: pontos_etapa
            $table->unsignedInteger('pontos_etapa')->nullable();
            $table->string('status_corrida')->default('nao_iniciada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados');
    }
};

