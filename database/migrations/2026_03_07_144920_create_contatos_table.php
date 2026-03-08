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
        Schema::create('contatos', function (Blueprint $table) {
            $table->id();
            $table->string('area');                    // Ex: Contato Comercial, Suporte Técnico
            $table->string('nome');                   // Nome do responsável
            $table->string('telefone')->nullable();    // WhatsApp (opcional)
            $table->string('icone', 80)->default('fa-solid fa-user'); // Classe Font Awesome
            $table->string('cor', 20)->default('orange'); // orange, blue, emerald, violet
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contatos');
    }
};
