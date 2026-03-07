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
        Schema::create('lancamentos_financeiros', function (Blueprint $table) {
            $table->id();

            // Vínculo com o evento
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            // Detalhes do Lançamento
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data');
            $table->string('categoria'); // Ex: Patrocínio, Marketing, etc.
            
            // Opcionais
            $table->string('comprovante_url')->nullable();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos_financeiros');
    }
};
