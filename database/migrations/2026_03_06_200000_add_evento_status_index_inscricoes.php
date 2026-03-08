<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índice composto para otimizar consultas por evento + status
     * (ex.: estatísticas do painel do evento, listagens filtradas).
     */
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->index(['evento_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropIndex(['evento_id', 'status']);
        });
    }
};
