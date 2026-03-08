<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_loja', function (Blueprint $table) {
            $table->foreignId('cupom_id')->nullable()->after('inscricao_id')->constrained('cupons')->nullOnDelete();
            $table->decimal('valor_desconto', 10, 2)->default(0)->after('taxa_servico');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_loja', function (Blueprint $table) {
            $table->dropForeign(['cupom_id']);
            $table->dropColumn(['valor_desconto']);
        });
    }
};
