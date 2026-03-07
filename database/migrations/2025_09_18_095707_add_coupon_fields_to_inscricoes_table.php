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
        Schema::table('inscricoes', function (Blueprint $table) {
            // Adiciona a chave estrangeira para o cupom.
            $table->foreignId('cupom_id')->nullable()->constrained('cupons')->nullOnDelete();

            // Guarda o valor original da inscrição, antes de qualquer desconto.
            $table->decimal('valor_original', 10, 2)->after('valor_pago')->default(0);
            
            // Guarda o valor do desconto aplicado.
            $table->decimal('valor_desconto', 10, 2)->after('valor_original')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropForeign(['cupom_id']);
            $table->dropColumn([
                'cupom_id',
                'valor_original',
                'valor_desconto',
            ]);
        });
    }
};