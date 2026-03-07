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
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();

            // Relação com o evento ao qual o cupom pertence.
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            // O código do cupom que o atleta irá usar.
            $table->string('codigo');

            // Tipo de desconto: 'percentual' ou 'fixo'.
            $table->enum('tipo_desconto', ['percentual', 'fixo']);
            $table->decimal('valor', 10, 2); // Guarda a porcentagem ou o valor fixo.

            // Limites de uso. Nullable significa que não há limite.
            $table->integer('limite_uso')->nullable();
            $table->integer('usos')->default(0); // Contador de quantas vezes foi usado.

            // Validade do cupom.
            $table->timestamp('data_validade')->nullable();

            // Status do cupom.
            $table->boolean('ativo')->default(true);

            $table->timestamps();

            // --- CORREÇÃO DE ROBUSTEZ APLICADA AQUI ---
            // Garante que o código do cupom seja único APENAS por evento.
            $table->unique(['evento_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupons');
    }
};

