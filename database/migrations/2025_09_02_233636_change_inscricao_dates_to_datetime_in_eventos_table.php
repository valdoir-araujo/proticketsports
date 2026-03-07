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
            // 1. Adicionar as novas colunas para ID de estado e cidade
            $table->foreignId('estado_id')->after('local')->constrained('estados');
            $table->foreignId('cidade_id')->after('estado_id')->constrained('cidades');

            // 2. Remover as colunas de texto antigas
            $table->dropColumn('cidade');
            $table->dropColumn('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Recria as colunas antigas se precisarmos de reverter
            $table->string('cidade');
            $table->string('estado', 2);

            // Remove as novas colunas e chaves estrangeiras
            $table->dropForeign(['cidade_id']);
            $table->dropForeign(['estado_id']);
            $table->dropColumn('cidade_id');
            $table->dropColumn('estado_id');
        });
    }
};
