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
            $table->decimal('TaxaServico', 5, 2)->nullable()->after('pontos_multiplicador')
                  ->comment('Percentual da taxa de serviço. Se NULL, usa o padrão do sistema.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('TaxaServico');
        });
    }
};
