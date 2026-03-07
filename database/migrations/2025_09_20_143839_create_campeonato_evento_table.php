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
        Schema::create('campeonato_evento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campeonato_id')->constrained('campeonatos')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            
            // Coluna para o multiplicador de pontos (Etapa Rainha)
            $table->decimal('pontos_multiplicador', 8, 2)->default(1.00);
            
            $table->timestamps();

            // Garante que um evento não possa ser adicionado duas vezes ao mesmo campeonato
            $table->unique(['campeonato_id', 'evento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campeonato_evento');
    }
};
