<?php

namespace App\Http\Controllers;

use App\Models\ProdutoOpcional;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function index()
    {
        // SEGURANÇA: Se o usuário entrar no carrinho, limpamos a sessão temporária de checkout.
        // Isso garante que ao clicar em "Ir para Pagamento", ele terá que se identificar novamente.
        // (Não afeta usuários logados com senha via Auth::login)
        if (session()->has('checkout_user_id')) {
            session()->forget('checkout_user_id');
        }

        return view('loja.carrinho');
    }

    public function adicionar(Request $request, $id)
    {
        $produto = ProdutoOpcional::findOrFail($id);
        $quantidade = (int) $request->input('quantidade', 1);

        $estoqueDisponivel = $produto->quantidade ?? $produto->estoque ?? $produto->limite_estoque ?? 999;
        
        $carrinho = session()->get('carrinho', []);

        if(isset($carrinho[$id])) {
            $novaQuantidade = $carrinho[$id]['quantidade'] + $quantidade;
            
            if ($estoqueDisponivel !== 999 && $novaQuantidade > $estoqueDisponivel) {
                return redirect()->back()->with('error', 'Quantidade solicitada indisponível no estoque!');
            }

            $carrinho[$id]['quantidade'] = $novaQuantidade;
        } else {
            if ($estoqueDisponivel !== 999 && $quantidade > $estoqueDisponivel) {
                return redirect()->back()->with('error', 'Quantidade solicitada indisponível no estoque!');
            }

            $carrinho[$id] = [
                "id" => $produto->id,
                "nome" => $produto->nome,
                "quantidade" => $quantidade,
                "preco" => $produto->valor,
                "imagem" => $produto->imagem_url
            ];
        }

        session()->put('carrinho', $carrinho);

        return redirect()->back()->with('success', 'Produto adicionado ao carrinho com sucesso!');
    }

    public function atualizar(Request $request)
    {
        if($request->id && $request->quantidade) {
            $carrinho = session()->get('carrinho');
            $id = $request->id;
            $novaQuantidade = (int) $request->quantidade;

            if ($novaQuantidade <= 0) {
                unset($carrinho[$id]);
                session()->put('carrinho', $carrinho);
                return redirect()->back()->with('success', 'Item removido do carrinho.');
            }

            if(isset($carrinho[$id])) {
                $produto = ProdutoOpcional::find($id);
                
                if ($produto) {
                    $estoqueDisponivel = $produto->quantidade ?? $produto->estoque ?? $produto->limite_estoque ?? 999;
                    
                    if ($estoqueDisponivel !== 999 && $novaQuantidade > $estoqueDisponivel) {
                        return redirect()->back()->with('error', 'Quantidade indisponível! Estoque máximo: ' . $estoqueDisponivel);
                    }
                }

                $carrinho[$id]['quantidade'] = $novaQuantidade;
                session()->put('carrinho', $carrinho);
                return redirect()->back()->with('success', 'Carrinho atualizado!');
            }
        }
        return redirect()->back();
    }

    public function remover(Request $request)
    {
        if($request->id) {
            $carrinho = session()->get('carrinho');
            if(isset($carrinho[$request->id])) {
                unset($carrinho[$request->id]);
                session()->put('carrinho', $carrinho);
            }
            return redirect()->back()->with('success', 'Produto removido do carrinho!');
        }
        return redirect()->back();
    }
}