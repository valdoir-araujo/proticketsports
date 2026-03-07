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
            // Adiciona a coluna para vincular as duplas
        $table->string('codigo_dupla')->nullable()->after('codigo_inscricao')->index();

        // Se quiser salvar o nome da dupla também (opcional, já que removemos do código principal):
        // $table->string('nome_dupla')->nullable()->after('codigo_dupla');
    
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropColumn('codigo_dupla');
        // $table->dropColumn('nome_dupla');
        });
    }
};
