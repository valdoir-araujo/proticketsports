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
            // Torna a coluna 'lote_inscricao_id' opcional (nullable)
            $table->foreignId('lote_inscricao_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            // Reverte a alteração se precisar de voltar atrás
            $table->foreignId('lote_inscricao_id')->nullable(false)->change();
        });
    }
};
