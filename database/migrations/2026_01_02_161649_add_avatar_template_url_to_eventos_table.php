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
        Schema::table('eventos', function (Blueprint $table) {
            // Só adiciona a coluna se ela NÃO existir no banco
            if (!Schema::hasColumn('eventos', 'avatar_template_url')) {
                $table->string('avatar_template_url')
                      ->nullable()
                      ->after('thumbnail_url')
                      ->comment('Caminho da imagem PNG com fundo transparente para gerar o avatar do atleta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Só tenta deletar se a coluna existir
            if (Schema::hasColumn('eventos', 'avatar_template_url')) {
                $table->dropColumn('avatar_template_url');
            }
        });
    }
};