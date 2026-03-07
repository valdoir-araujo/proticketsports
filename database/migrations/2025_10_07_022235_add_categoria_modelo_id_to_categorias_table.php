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
        Schema::table('categorias', function (Blueprint $table) {
            // Adiciona a coluna que irá ligar a categoria de um evento ao seu modelo mestre.
            $table->foreignId('categoria_modelo_id')->nullable()->after('percurso_id')->constrained('categoria_modelos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['categoria_modelo_id']);
            $table->dropColumn('categoria_modelo_id');
        });
    }
};
