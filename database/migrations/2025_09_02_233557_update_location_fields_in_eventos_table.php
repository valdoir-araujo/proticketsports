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
        Schema::table('eventos', function (Blueprint $table) {
            // Altera as colunas de 'date' para 'datetime'
            $table->dateTime('data_inicio_inscricoes')->change();
            $table->dateTime('data_fim_inscricoes')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Reverte as colunas para 'date' se necessário
            $table->date('data_inicio_inscricoes')->change();
            $table->date('data_fim_inscricoes')->change();
        });
    }
};
