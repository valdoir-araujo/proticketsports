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
        Schema::create('percurso_modelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacao_id')->constrained()->cascadeOnDelete();
            $table->string('descricao'); // Ex: "Percurso Pro", "Percurso Sport"
            $table->string('codigo');      // Ex: "PRO", "SPORT"
            $table->timestamps();

            // Garante que o código é único para cada organização
            $table->unique(['organizacao_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percurso_modelos');
    }
};
