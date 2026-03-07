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
            // A coluna deve ser nullable, pois nem todo evento pertence a um campeonato
            $table->foreignId('campeonato_id')
                  ->nullable()
                  ->after('id') // Posiciona a coluna para melhor organização
                  ->constrained('campeonatos')
                  ->nullOnDelete(); // Se um campeonato for apagado, os eventos ficam "avulsos"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna
            $table->dropForeign(['campeonato_id']);
            $table->dropColumn('campeonato_id');
        });
    }
};
