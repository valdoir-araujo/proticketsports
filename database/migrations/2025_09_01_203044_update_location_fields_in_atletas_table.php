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
        Schema::table('atletas', function (Blueprint $table) {
            // Adiciona as novas colunas de chave estrangeira
            $table->foreignId('cidade_id')->nullable()->after('sexo')->constrained('cidades');
            $table->foreignId('estado_id')->nullable()->after('cidade_id')->constrained('estados');

            // Remove as colunas de texto antigas que não são mais necessárias
            if (Schema::hasColumn('atletas', 'cidade')) {
                $table->dropColumn('cidade');
            }
            if (Schema::hasColumn('atletas', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atletas', function (Blueprint $table) {
            // Reverte as alterações: remove as chaves estrangeiras e recria as colunas antigas
            $table->dropForeign(['cidade_id']);
            $table->dropForeign(['estado_id']);
            $table->dropColumn(['cidade_id', 'estado_id']);

            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
        });
    }
};
