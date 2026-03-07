<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RankingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe o ranking do campeonato.
     */
    public function index(Campeonato $campeonato): View
    {
        // 1. Carregar todas as etapas (eventos) do campeonato
        $etapas = $campeonato->eventos()
            ->where('status', '!=', 'cancelado')
            ->orderBy('data_evento')
            ->get();
            
        $eventoIds = $etapas->pluck('id');

        // 2. Buscar todas as inscrições confirmadas que tenham resultados
        $inscricoes = Inscricao::with(['atleta.user', 'atleta.cidade.estado', 'categoria.percurso', 'equipe', 'resultado', 'evento'])
            ->whereIn('evento_id', $eventoIds)
            ->where('status', 'confirmada')
            ->has('resultado') // Garante que só pega quem tem resultado lançado
            ->get();

        // -------------------------------------------------------
        // LÓGICA DO RANKING INDIVIDUAL
        // -------------------------------------------------------
        
        $rankingIndividual = $inscricoes->groupBy('atleta_id')->map(function ($inscricoesDoAtleta) {
            // Pega a inscrição mais recente para definir a categoria atual do atleta no ranking
            $ultimaInscricao = $inscricoesDoAtleta->sortByDesc('evento.data_evento')->first();
            
            // Validação de segurança: se atleta ou categoria foram deletados, ignora
            if (!$ultimaInscricao || !$ultimaInscricao->atleta || !$ultimaInscricao->categoria) {
                return null;
            }

            $atleta = $ultimaInscricao->atleta;
            $categoria = $ultimaInscricao->categoria;
            $percurso = $categoria->percurso;

            // Mapeia os pontos por etapa
            $pontosPorEtapa = $inscricoesDoAtleta->mapWithKeys(function ($inscricao) {
                // Usa o operador ?? 0 para garantir que não quebre se pontos_etapa for nulo
                return [$inscricao->evento_id => $inscricao->resultado->pontos_etapa ?? 0];
            });

            return [
                'atleta' => $atleta,
                'percurso_nome' => $percurso->descricao ?? 'Geral',
                'categoria_nome' => $categoria->nome,
                'genero' => $categoria->genero,
                'pontos_totais' => $inscricoesDoAtleta->sum(fn($i) => $i->resultado->pontos_etapa ?? 0),
                'pontos_por_etapa' => $pontosPorEtapa,
            ];
        })
        ->filter() // Remove nulos
        ->values();

        // Agrupa para a View (Percurso -> Gênero -> Categoria -> Atletas)
        $rankingAgrupado = $rankingIndividual
            ->groupBy('percurso_nome')
            ->map(function ($atletasDoPercurso) {
                return $atletasDoPercurso->groupBy('genero')
                    ->map(function ($atletasDoGenero) {
                        return $atletasDoGenero->groupBy('categoria_nome')
                            ->map(function ($atletasDaCategoria, $catNome) {
                                // Ordena os atletas dentro da categoria por pontos
                                return $atletasDaCategoria->sortByDesc('pontos_totais')->values();
                            });
                    });
            });

        // -------------------------------------------------------
        // LÓGICA DO RANKING DE EQUIPES
        // -------------------------------------------------------
        
        // 1. Filtra inscrições que têm equipe vinculada
        $inscricoesComEquipe = $inscricoes->whereNotNull('equipe_id');

        $rankingEquipes = $inscricoesComEquipe
            ->groupBy('equipe_id')
            ->map(function ($inscricoesDaEquipe) {
                // Pega a equipe da primeira inscrição encontrada
                $equipe = $inscricoesDaEquipe->first()->equipe;
                
                // SEGURANÇA: Se a equipe foi excluída do banco, retorna null para ser filtrada depois
                if (!$equipe) return null;

                // Soma pontos por etapa (agrupando todas as inscrições daquela equipe naquele evento)
                $pontosPorEtapa = $inscricoesDaEquipe->groupBy('evento_id')
                    ->mapWithKeys(fn($ins, $evtId) => [
                        $evtId => $ins->sum(fn($i) => $i->resultado->pontos_etapa ?? 0)
                    ]);

                return [
                    'equipe' => $equipe,
                    'pontos_totais' => $inscricoesDaEquipe->sum(fn($i) => $i->resultado->pontos_etapa ?? 0),
                    'pontos_por_etapa' => $pontosPorEtapa
                ];
            })
            ->filter() // REMOVE EQUIPES NULAS (Isso corrige o erro "nome on null")
            ->sortByDesc('pontos_totais')
            ->values();

        return view('organizador.campeonatos.ranking.index', compact(
            'campeonato', 
            'etapas', 
            'rankingAgrupado', 
            'rankingEquipes'
        ));
    }
}