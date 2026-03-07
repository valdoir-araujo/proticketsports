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
        Schema::create('lotes_inscricao', function (Blueprint $table) {
        $table->id();
        // Um lote de preço agora pertence a uma Categoria (Master A1, Elite, etc.)
        $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();

        $table->string('descricao'); // Ex: "1º Lote", "Lote Promocional"
        $table->decimal('valor', 10, 2);
        $table->date('data_inicio');
        $table->date('data_fim');

        $table->timestamps();
    });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes_inscricao');
    }
};
