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
        Schema::table('regras_pontuacao', function (Blueprint $table) {
            // Adiciona as colunas para percurso e categoria, que podem ser nulas
                $table->foreignId('percurso_id')->nullable()->constrained()->onDelete('cascade')->after('campeonato_id');
                $table->foreignId('categoria_id')->nullable()->constrained()->onDelete('cascade')->after('percurso_id');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regras_pontuacao', function (Blueprint $table) {
            $table->dropForeign(['percurso_id']);
            $table->dropForeign(['categoria_id']);
            $table->dropColumn(['percurso_id', 'categoria_id']);
        });
    }
};
