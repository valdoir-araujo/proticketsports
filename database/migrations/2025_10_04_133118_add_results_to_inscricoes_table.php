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
            $table->time('tempo_conclusao')->nullable()->after('status');
                $table->unsignedInteger('posicao_categoria')->nullable()->after('tempo_conclusao');
                $table->unsignedInteger('pontos_campeonato')->nullable()->after('posicao_categoria');
                $table->string('status_corrida')->default('nao_iniciada')->after('pontos_campeonato'); // Ex: completou, nao_completou, desqualificado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropColumn(['tempo_conclusao', 'posicao_categoria', 'pontos_campeonato', 'status_corrida']);
        });
    }
};
