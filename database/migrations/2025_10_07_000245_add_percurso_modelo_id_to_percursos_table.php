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
        Schema::table('percursos', function (Blueprint $table) {
            // Adiciona a coluna que irá ligar o percurso de um evento ao seu modelo mestre.
            // É "nullable" para ser compatível com os percursos que já existem.
            $table->foreignId('percurso_modelo_id')->nullable()->after('evento_id')->constrained('percurso_modelos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('percursos', function (Blueprint $table) {
            $table->dropForeign(['percurso_modelo_id']);
            $table->dropColumn('percurso_modelo_id');
        });
    }
};
