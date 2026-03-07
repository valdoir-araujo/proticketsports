<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\ProdutoOpcional; 
use Illuminate\Http\Request;

class LojaController extends Controller
{
    public function index(Request $request)
    {
        // Verifica se o ID do evento foi passado na URL (ex: /loja?evento_id=1)
        if (!$request->has('evento_id')) {
            // Se não tiver evento, redireciona para a lista de eventos
            return redirect()->route('eventos.public.index');
        }

        // Busca o evento
        $evento = Evento::findOrFail($request->evento_id);

        // Busca os produtos desse evento. 
        // Estamos usando ProdutoOpcional pois vi que você tem 'ProdutoOpcionalController' nas rotas.
        $produtos = ProdutoOpcional::where('evento_id', $evento->id)
                        ->where('ativo', true) // Supondo que exista um campo 'ativo'
                        ->get();

        // --- LÓGICA DO CARRINHO (ADICIONADA) ---
        // Recupera o carrinho da sessão para calcular os totais do cabeçalho
        $carrinho = session()->get('carrinho', []);
        $totalCarrinho = 0;
        $qtdCarrinho = 0;

        foreach($carrinho as $item) {
            $totalCarrinho += $item['preco'] * $item['quantidade'];
            $qtdCarrinho += $item['quantidade'];
        }
        // ---------------------------------------

        // Retorna a view com as variáveis do carrinho ($totalCarrinho e $qtdCarrinho)
        return view('loja.index', compact('evento', 'produtos', 'totalCarrinho', 'qtdCarrinho'));
    }
}