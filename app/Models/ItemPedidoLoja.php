<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPedidoLoja extends Model
{
    protected $table = 'itens_pedido_loja';

    protected $fillable = [
        'pedido_loja_id', 'produto_opcional_id', 
        'quantidade', 'valor_unitario', 'tamanho'
    ];

    public function produto()
    {
        return $this->belongsTo(ProdutoOpcional::class, 'produto_opcional_id');
    }
}