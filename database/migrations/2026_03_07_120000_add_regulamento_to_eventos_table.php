<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('regulamento_tipo', 10)->nullable()->comment('pdf ou texto');
            $table->string('regulamento_arquivo')->nullable()->comment('caminho do PDF no storage');
            $table->longText('regulamento_texto')->nullable()->comment('conteúdo em HTML quando tipo=texto');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['regulamento_tipo', 'regulamento_arquivo', 'regulamento_texto']);
        });
    }
};
