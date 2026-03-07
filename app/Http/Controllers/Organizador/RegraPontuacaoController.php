<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use App\Models\RegraPontuacao;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class RegraPontuacaoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a página para gerenciar as regras de pontuação de um campeonato.
     */
    public function index(Campeonato $campeonato): View
    {
        $this->authorize('view', $campeonato);

        // Carrega o campeonato com toda a hierarquia de eventos, percursos e categorias
        $campeonato->load('eventos.percursos.categorias');

        // Carrega todas as regras de pontuação associadas a este campeonato
        $allRules = $campeonato->regrasPontuacao()->get();

        // Agrupa as regras para a nova interface de abas
        $regrasGerais = $allRules->whereNull('percurso_id')->whereNull('categoria_id')->sortBy('posicao');
        $regrasPorPercurso = $allRules->whereNotNull('percurso_id')->whereNull('categoria_id')->groupBy('percurso_id');
        $regrasPorCategoria = $allRules->whereNotNull('categoria_id')->groupBy('categoria_id');
        
        // CORREÇÃO: A view antiga espera uma variável chamada '$regras'.
        // Vamos passar as regras gerais com esse nome para garantir a compatibilidade.
        $regras = $regrasGerais;

        return view('organizador.campeonatos.regras.index', compact(
            'campeonato',
            'regras', // Para compatibilidade com a view atual
            'regrasGerais',
            'regrasPorPercurso',
            'regrasPorCategoria'
        ));
    }

    /**
     * Salva ou atualiza todas as regras de pontuação de um campeonato.
     */
    public function store(Request $request, Campeonato $campeonato): RedirectResponse
    {
        $this->authorize('update', $campeonato);

        $validated = $request->validate([
            'regras_gerais' => 'nullable|array',
            'regras_gerais.*.posicao' => 'required_with:regras_gerais|integer|min:1',
            'regras_gerais.*.pontos' => 'required_with:regras_gerais|integer|min:0',
            
            'regras_percurso' => 'nullable|array',
            'regras_percurso.*.*.posicao' => 'required|integer|min:1',
            'regras_percurso.*.*.pontos' => 'required|integer|min:0',

            'regras_categoria' => 'nullable|array',
            'regras_categoria.*.*.posicao' => 'required|integer|min:1',
            'regras_categoria.*.*.pontos' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($campeonato, $validated) {
            // Apaga todas as regras antigas para simplificar
            $campeonato->regrasPontuacao()->delete();

            // Salva as regras gerais
            if (!empty($validated['regras_gerais'])) {
                foreach ($validated['regras_gerais'] as $regra) {
                    $campeonato->regrasPontuacao()->create($regra);
                }
            }

            // Salva as regras por percurso
            if (!empty($validated['regras_percurso'])) {
                foreach ($validated['regras_percurso'] as $percursoId => $regras) {
                    foreach ($regras as $regra) {
                        $campeonato->regrasPontuacao()->create(array_merge($regra, ['percurso_id' => $percursoId]));
                    }
                }
            }

            // Salva as regras por categoria
            if (!empty($validated['regras_categoria'])) {
                foreach ($validated['regras_categoria'] as $categoriaId => $regras) {
                    foreach ($regras as $regra) {
                        $campeonato->regrasPontuacao()->create(array_merge($regra, ['categoria_id' => $categoriaId]));
                    }
                }
            }
        });
        
        return redirect()->route('organizador.campeonatos.regras.index', $campeonato)
                         ->with('sucesso', 'Regras de pontuação salvas com sucesso!');
    }
}

