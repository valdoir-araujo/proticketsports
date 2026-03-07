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
        Schema::table('produtos_opcionais', function (Blueprint $table) {
            $table->string('imagem_url')->nullable()->after('nome');
            $table->boolean('ativo')->default(true)->after('requer_tamanho');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos_opcionais', function (Blueprint $table) {
            $table->dropColumn(['imagem_url', 'ativo']);
        });
    }
};

