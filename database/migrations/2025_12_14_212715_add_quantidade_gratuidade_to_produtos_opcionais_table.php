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
        Schema::table('produtos_opcionais', function (Blueprint $table) {
            // Adiciona a coluna após 'limite_estoque'
            $table->integer('quantidade_gratuidade')
                  ->nullable()
                  ->default(0)
                  ->after('limite_estoque')
                  ->comment('Quantidade de itens gratuitos para os primeiros inscritos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos_opcionais', function (Blueprint $table) {
            $table->dropColumn('quantidade_gratuidade');
        });
    }
};
