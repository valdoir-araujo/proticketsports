<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atletas', function (Blueprint $table) {
            $table->foreignId('equipe_id')->nullable()->constrained('equipes')->nullOnDelete();
            $table->string('contato_emergencia_nome')->nullable();
            $table->string('contato_emergencia_telefone', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('atletas', function (Blueprint $table) {
            $table->dropForeign(['equipe_id']);
            $table->dropColumn([
                'equipe_id',
                'contato_emergencia_nome',
                'contato_emergencia_telefone',
            ]);
        });
    }
};