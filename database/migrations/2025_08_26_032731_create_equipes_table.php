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
        Schema::create('equipes', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            // 'coordenador_id' se relaciona com a coluna 'id' na tabela 'atletas'
            $table->foreignId('coordenador_id')->constrained('atletas')->restrictOnDelete();       

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipes');
    }
};
