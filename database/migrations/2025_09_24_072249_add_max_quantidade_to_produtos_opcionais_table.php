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
        Schema::table('produtos_opcionais', function (Blueprint $table) {
            $table->integer('max_quantidade_por_inscricao')->unsigned()->nullable()->after('limite_estoque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos_opcionais', function (Blueprint $table) {
            $table->dropColumn('max_quantidade_por_inscricao');
        });
    }
};
