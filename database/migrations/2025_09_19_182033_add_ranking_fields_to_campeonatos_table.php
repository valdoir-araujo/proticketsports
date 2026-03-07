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
            $table->boolean('gera_ranking_geral')->default(false)->after('descricao');
            $table->integer('posicoes_para_ranking_geral')->nullable()->after('gera_ranking_geral')->comment('Número de posições (ex: 5 para top 5) de cada categoria que pontuam no geral.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campeonatos', function (Blueprint $table) {
            $table->dropColumn(['gera_ranking_geral', 'posicoes_para_ranking_geral']);
        });
    }
};
