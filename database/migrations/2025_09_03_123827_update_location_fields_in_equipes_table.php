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
        Schema::table('equipes', function (Blueprint $table) {
            // 1. Adiciona as novas colunas de chave estrangeira
            $table->foreignId('estado_id')->nullable()->after('coordenador_id')->constrained('estados');
            $table->foreignId('cidade_id')->nullable()->after('estado_id')->constrained('cidades');

            // 2. Remove as colunas de texto antigas
            $table->dropColumn(['cidade', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            // 1. Adiciona as colunas antigas de volta
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            // 2. Remove as novas chaves estrangeiras e colunas
            $table->dropForeign(['cidade_id']);
            $table->dropForeign(['estado_id']);
            $table->dropColumn(['cidade_id', 'estado_id']);
        });
    }
};

