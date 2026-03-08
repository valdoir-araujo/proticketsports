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
        // 1. Adicionar estado_id e cidade_id só se não existirem (evita duplicate column)
        if (!Schema::hasColumn('eventos', 'estado_id')) {
            Schema::table('eventos', function (Blueprint $table) {
                $table->foreignId('estado_id')->after('local')->constrained('estados');
            });
        }
        if (!Schema::hasColumn('eventos', 'cidade_id')) {
            Schema::table('eventos', function (Blueprint $table) {
                $table->foreignId('cidade_id')->after('estado_id')->constrained('cidades');
            });
        }

        // 2. Remover colunas de texto antigas só se existirem
        $drop = [];
        if (Schema::hasColumn('eventos', 'cidade')) {
            $drop[] = 'cidade';
        }
        if (Schema::hasColumn('eventos', 'estado')) {
            $drop[] = 'estado';
        }
        if (!empty($drop)) {
            Schema::table('eventos', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Recria as colunas antigas se precisarmos de reverter
            $table->string('cidade');
            $table->string('estado', 2);

            // Remove as novas colunas e chaves estrangeiras
            $table->dropForeign(['cidade_id']);
            $table->dropForeign(['estado_id']);
            $table->dropColumn('cidade_id');
            $table->dropColumn('estado_id');
        });
    }
};
