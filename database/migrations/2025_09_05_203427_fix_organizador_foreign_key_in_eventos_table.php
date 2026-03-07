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
            // 1. Apagar a chave estrangeira antiga que aponta para a tabela 'organizadores'
            // O nome da constraint pode variar, mas o Laravel geralmente cria um padrão.
            // Se este nome não funcionar, verifique o nome exato na sua base de dados.
            $table->dropForeign('eventos_organizador_id_foreign');

            // 2. Criar a nova chave estrangeira correta, que aponta para a tabela 'users'
            $table->foreign('organizador_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade'); // Se um utilizador for apagado, os seus eventos também serão.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Desfaz as alterações: apaga a nova chave e recria a antiga.
            $table->dropForeign(['organizador_id']);

            $table->foreign('organizador_id')
                  ->references('id')
                  ->on('organizadores') // Aponta de volta para a tabela original (incorreta)
                  ->onDelete('cascade');
        });
    }
};

