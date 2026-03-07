<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->string('codigo_grupo')->nullable()->after('codigo_dupla')->index();
            $table->string('tipo_pagamento_grupo', 20)->nullable()->after('codigo_grupo'); // 'unico' | 'individual'
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropColumn(['codigo_grupo', 'tipo_pagamento_grupo']);
        });
    }
};
