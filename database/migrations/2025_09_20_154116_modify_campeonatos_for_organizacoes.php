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
        Schema::table('campeonatos', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna antiga
            $table->dropForeign(['organizador_id']);
            $table->dropColumn('organizador_id');

            // Adiciona a nova coluna e chave estrangeira
            $table->foreignId('organizacao_id')->after('id')->constrained('organizacoes');
            
            // ==========================================================
            // ÍNDICE ADICIONADO AQUI PARA MELHORAR A PERFORMANCE
            // ==========================================================
            $table->index('organizacao_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campeonatos', function (Blueprint $table) {
            // Reverte as alterações na ordem inversa
            $table->dropForeign(['organizacao_id']);
            $table->dropIndex(['organizacao_id']); // Remove o índice
            $table->dropColumn('organizacao_id');
            $table->foreignId('organizador_id')->constrained('users');
        });
    }
};

