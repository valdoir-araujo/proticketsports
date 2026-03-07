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
           // Verifica se a coluna NÃO existe antes de a adicionar
            if (!Schema::hasColumn('inscricoes', 'repasse_id')) {
                $table->foreignId('repasse_id')->nullable()->constrained()->after('transacao_id_gateway');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            //
        });
    }
};
