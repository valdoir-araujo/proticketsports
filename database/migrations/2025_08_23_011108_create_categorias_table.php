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
        Schema::create('categorias', function (Blueprint $table) {
        $table->id();
        $table->foreignId('percurso_id')->constrained('percursos')->cascadeOnDelete();

        $table->string('nome'); // Ex: "Master A1", "Elite Feminino", "Dupla Mista"

        // Regras de elegibilidade
        $table->unsignedSmallInteger('ano_nascimento_min')->nullable();
        $table->unsignedSmallInteger('ano_nascimento_max')->nullable();
        $table->unsignedTinyInteger('idade_minima')->nullable();
        $table->unsignedTinyInteger('idade_maxima')->nullable();
        $table->enum('genero', ['masculino', 'feminino', 'misto', 'unissex'])->default('unissex');

        $table->unsignedInteger('vagas_disponiveis')->nullable();

        $table->timestamps();
    });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
