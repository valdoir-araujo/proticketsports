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
        Schema::create('equipe_user', function (Blueprint $table) {
            $table->id();
        
        // Chave estrangeira para User
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Chave estrangeira para Equipe
        $table->foreignId('equipe_id')->constrained('equipes')->onDelete('cascade');
        
        // Importante: O erro mostra que o sistema busca 'created_at' na pivô
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipe_user');
    }
};
