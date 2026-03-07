<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProdutoOpcional extends Model
{
    use HasFactory;

    protected $table = 'produtos_opcionais';

    protected $fillable = [
        'evento_id',
        'nome',
        'descricao',
        'imagem_url',
        'valor',
        'limite_estoque',
        'quantidade_gratuidade',
        'max_quantidade_por_inscricao',
        'requer_tamanho',
        'ativo',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Relacionamento com Inscrições.
     * NOME CORRETO DA TABELA: 'inscricao_produto'
     */
    public function inscricoes()
    {
        return $this->belongsToMany(Inscricao::class, 'inscricao_produto', 'produto_opcional_id', 'inscricao_id')
                    ->withPivot('valor_pago_por_item', 'quantidade', 'tamanho')
                    ->withTimestamps();
    }

    /**
     * Lógica 1: Conta quantas gratuidades já foram consumidas.
     */
    public function getGratuidadesConsumidasAttribute()
    {
        if (!$this->quantidade_gratuidade || $this->quantidade_gratuidade <= 0) {
            return 0;
        }

        // CORREÇÃO FINAL: Tabela 'inscricao_produto' e coluna 'valor_pago_por_item'
        return DB::table('inscricao_produto')
            ->join('inscricoes', 'inscricao_produto.inscricao_id', '=', 'inscricoes.id')
            ->where('inscricao_produto.produto_opcional_id', $this->id)
            ->where('inscricao_produto.valor_pago_por_item', 0) // Verifica se foi brinde
            ->whereIn('inscricoes.status', ['confirmada', 'aguardando_pagamento', 'pendente'])
            ->sum('inscricao_produto.quantidade');
    }

    public function getTemGratuidadeDisponivelAttribute()
    {
        if (!$this->quantidade_gratuidade || $this->quantidade_gratuidade <= 0) {
            return false;
        }

        return $this->gratuidades_consumidas < $this->quantidade_gratuidade;
    }
}