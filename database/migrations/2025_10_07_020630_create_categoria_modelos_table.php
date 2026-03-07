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
        Schema::create('categoria_modelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacao_id')->constrained()->cascadeOnDelete();
            $table->foreignId('percurso_modelo_id')->constrained('percurso_modelos')->cascadeOnDelete();
            $table->string('nome');
            $table->string('codigo');
            $table->enum('genero', ['Masculino', 'Feminino', 'Unissex']);
            $table->integer('idade_min')->default(0);
            $table->integer('idade_max')->default(99);
            $table->timestamps();

            // --- CORREÇÃO APLICADA ---
            // Adiciona um nome curto e explícito para o índice unique para evitar o erro.
            $table->unique(
                ['organizacao_id', 'percurso_modelo_id', 'codigo'],
                'cat_modelo_org_perc_cod_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_modelos');
    }
};