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
        Schema::table('inscricoes', function (Blueprint $table) {
             $table->foreignId('repasse_id')->nullable()->after('transacao_id_gateway')
              ->constrained('repasses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            // A convenção de nome do Laravel é 'tabela_coluna_foreign'
            $table->dropForeign(['repasse_id']);
            $table->dropColumn('repasse_id');
        });
    }
};
