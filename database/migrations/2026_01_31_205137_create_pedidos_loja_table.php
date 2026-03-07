<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos_loja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos');
            $table->foreignId('user_id')->constrained('users');
            // Aqui está o vínculo opcional: pode ser nulo se for venda avulsa
            $table->foreignId('inscricao_id')->nullable()->constrained('inscricoes')->nullOnDelete();
            
            $table->decimal('valor_total', 10, 2);
            $table->decimal('taxa_servico', 10, 2)->default(0);
            $table->string('status')->default('pendente'); // pendente, pago, cancelado
            
            $table->string('gateway_payment_id')->nullable(); // ID do pagamento no MercadoPago
            $table->string('forma_pagamento')->nullable(); // pix, credit_card
            $table->timestamps();
        });

        Schema::create('itens_pedido_loja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_loja_id')->constrained('pedidos_loja')->onDelete('cascade');
            // Ajuste o nome da tabela 'produto_opcionals' conforme seu banco real
            $table->foreignId('produto_opcional_id')->constrained('produto_opcionals'); 
            
            $table->integer('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->string('tamanho')->nullable(); // P, M, G, etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('itens_pedido_loja');
        Schema::dropIfExists('pedidos_loja');
    }
};