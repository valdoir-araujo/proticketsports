<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoLoja extends Model
{
    protected $table = 'pedidos_loja';

    protected $fillable = [
        'evento_id', 'user_id', 'inscricao_id', 'cupom_id',
        'valor_total', 'taxa_servico', 'valor_desconto', 'status',
        'gateway_payment_id', 'forma_pagamento'
    ];

    public function itens()
    {
        return $this->hasMany(ItemPedidoLoja::class, 'pedido_loja_id');
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cupom()
    {
        return $this->belongsTo(\App\Models\Cupom::class);
    }
}