<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricao_produto', function (Blueprint $table) {
            $table->boolean('entregue')->default(true)->after('tamanho');
        });
    }

    public function down(): void
    {
        Schema::table('inscricao_produto', function (Blueprint $table) {
            $table->dropColumn('entregue');
        });
    }
};
