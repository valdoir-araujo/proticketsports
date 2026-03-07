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
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id();

            // --- Chaves Estrangeiras (As Conexões) ---
            $table->foreignId('atleta_id')->constrained('atletas')->restrictOnDelete();
            $table->foreignId('evento_id')->constrained('eventos')->restrictOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('lote_inscricao_id')->constrained('lotes_inscricao')->restrictOnDelete();
            $table->foreignId('equipe_id')->nullable()->constrained('equipes')->nullOnDelete(); // <-- COLUNA ADICIONADA AQUI

            // --- Dados da Inscrição ---
            $table->string('codigo_inscricao')->unique();
            $table->enum('status', ['pendente', 'confirmada', 'cancelada', 'aguardando_pagamento'])->default('aguardando_pagamento')->index();

            // --- Dados Financeiros (Snapshot) ---
            $table->decimal('valor_pago', 10, 2);
            $table->timestamp('data_pagamento')->nullable();
            $table->string('metodo_pagamento', 50)->nullable();
            $table->string('transacao_id_gateway')->nullable()->index();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscricoes');
    }
};
