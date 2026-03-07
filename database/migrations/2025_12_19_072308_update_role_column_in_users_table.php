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
        Schema::table('users', function (Blueprint $table) {
            // Altera a coluna 'role' para VARCHAR(50) para aceitar 'staff' e futuros cargos
            // O método change() requer que a coluna já exista
            $table->string('role', 50)->default('atleta')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Se precisar reverter, podemos voltar para string ou enum anterior
        // (Deixamos como string por segurança)
    }
};