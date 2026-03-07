<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\LoteInscricao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoteController extends Controller
{
    /**
     * Exibe a página de gestão de lotes para uma categoria específica.
     */
    public function index(Categoria $categoria): View
    {
        // ==========================================================
        // LÓGICA DE SEGURANÇA CORRIGIDA AQUI
        // Verifica se o usuário logado é membro da organização dona do evento.
        // ==========================================================
        if (!Auth::user()->organizacoes->contains($categoria->percurso->evento->organizacao)) {
            abort(403, 'Acesso Não Autorizado.');
        }

        // Carrega os lotes existentes para a categoria
        $categoria->load('lotesInscricao');

        return view('organizador.lotes.index', compact('categoria'));
    }

    /**
     * Salva um novo lote de preço para uma categoria.
     */
    public function store(Request $request, Categoria $categoria): RedirectResponse
    {
        // LÓGICA DE SEGURANÇA CORRIGIDA
        if (!Auth::user()->organizacoes->contains($categoria->percurso->evento->organizacao)) {
            abort(403, 'Acesso Não Autorizado.');
        }

        // Validação
        $dadosValidados = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        // Criação
        $categoria->lotesInscricao()->create($dadosValidados);

        return redirect()->route('organizador.lotes.index', $categoria)
                         ->with('sucesso', 'Lote de preço adicionado com sucesso!');
    }
    
    /**
     * Exclui um lote de inscrição.
     */
    public function destroy(LoteInscricao $lote): RedirectResponse
    {
        // LÓGICA DE SEGURANÇA CORRIGIDA
        // Acessa a organização através das relações: Lote -> Categoria -> Percurso -> Evento -> Organização
        if (!Auth::user()->organizacoes->contains($lote->categoria->percurso->evento->organizacao)) {
            abort(403, 'Acesso Não Autorizado.');
        }
        
        $categoria = $lote->categoria;
        $lote->delete();

        return redirect()->route('organizador.lotes.index', $categoria)
                         ->with('sucesso', 'Lote excluído com sucesso!');
    }
}
