<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Percurso;
use App\Models\Categoria;
use App\Models\CategoriaModelo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a página para gerir as categorias de um percurso.
     */
    public function index(Percurso $percurso): View | RedirectResponse
    {
        $this->authorize('update', $percurso->evento);

        if (!$percurso->percurso_modelo_id) {
            return redirect()->route('organizador.eventos.show', ['evento' => $percurso->evento, 'tab' => 'percursos'])
                ->withErrors(['msg' => "O percurso '{$percurso->descricao}' é um registo antigo. Por favor, vincule-o a um modelo da sua biblioteca para poder gerir as suas categorias."]);
        }

        $percurso->load('categorias');

        $categoriasJaAdicionadasIds = $percurso->categorias->pluck('categoria_modelo_id')->filter();

        $categoriaModelosDisponiveis = CategoriaModelo::where('percurso_modelo_id', $percurso->percurso_modelo_id)
            ->whereNotIn('id', $categoriasJaAdicionadasIds)
            ->orderBy('nome')
            ->get();

        return view('organizador.categorias.index', compact('percurso', 'categoriaModelosDisponiveis'));
    }

    /**
     * Adiciona uma ou mais categorias de um modelo a um percurso de evento.
     */
    public function store(Request $request, Percurso $percurso): RedirectResponse
    {
        $this->authorize('update', $percurso->evento);

        $validated = $request->validate([
            'categoria_modelos' => 'required|array',
            'categoria_modelos.*' => 'exists:categoria_modelos,id',
        ], [
            'categoria_modelos.required' => 'Você precisa selecionar pelo menos uma categoria para adicionar.'
        ]);

        $modelosSelecionados = CategoriaModelo::find($validated['categoria_modelos']);

        foreach ($modelosSelecionados as $modelo) {
            // --- ESTA É A CORREÇÃO ---
            // Mapeia os campos do modelo (idade_min/idade_max) para os campos corretos da tabela (idade_minima/idade_maxima).
            $percurso->categorias()->create([
                'categoria_modelo_id' => $modelo->id,
                'nome' => $modelo->nome,
                'genero' => $modelo->genero,
                'idade_minima' => $modelo->idade_min, // Corrigido
                'idade_maxima' => $modelo->idade_max, // Corrigido
            ]);
        }

        return back()->with('sucesso', count($modelosSelecionados) . ' categoria(s) adicionada(s) com sucesso!');
    }

    /**
     * Exclui uma categoria de um percurso.
     */
    public function destroy(Percurso $percurso, Categoria $categoria): RedirectResponse
    {
        $this->authorize('update', $percurso->evento);
        
        if ($categoria->inscricoes()->exists()) {
            return back()->withErrors(['msg' => 'Não é possível remover uma categoria que já possui inscritos.']);
        }

        $categoria->delete();

        return redirect()->route('organizador.categorias.index', $percurso)
                         ->with('sucesso', 'Categoria excluída com sucesso!');
    }
}

