<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->string('ritmo_previsto', 50)->nullable()->after('codigo_grupo')->comment('Ritmo previsto em min/km, ex: 5:30');
            $table->string('pelotao_largada', 50)->nullable()->after('ritmo_previsto')->comment('Pelotão/onda de largada, ex: A, B, 1');
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropColumn(['ritmo_previsto', 'pelotao_largada']);
        });
    }
};
