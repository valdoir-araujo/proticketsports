<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CampeonatoPublicoController extends Controller
{
    /**
     * Exibe a lista de campeonatos em andamento (status ativo) no site público.
     */
    public function index(Request $request): View
    {
        $campeonatos = Campeonato::with(['organizacao'])
            ->withCount('eventos')
            ->where('status', 'ativo')
            ->orderBy('ano', 'desc')
            ->orderBy('nome', 'asc')
            ->paginate(12);

        return view('campeonatos.index', compact('campeonatos'));
    }

    /**
     * Exibe os detalhes de um campeonato específico (Etapas e Rankings).
     */
    public function show(Campeonato $campeonato): View
    {
        if ($campeonato->status === 'cancelado') {
            abort(404);
        }

        $campeonato->load(['organizacao']);

        $etapas = Evento::where('campeonato_id', $campeonato->id)
            ->with('cidade.estado')
            ->orderBy('data_evento', 'asc')
            ->get();

        $rankingAtletas = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')
            ->join('users', 'atletas.user_id', '=', 'users.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->whereNotNull('resultados.pontos_etapa')
            ->select('atletas.id as atleta_id', 'users.name as nome_atleta', DB::raw('SUM(resultados.pontos_etapa) as total_pontos'))
            ->groupBy('atletas.id', 'users.name')
            ->orderByDesc('total_pontos')
            ->get();

        $rankingEquipes = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('equipes', 'inscricoes.equipe_id', '=', 'equipes.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->whereNotNull('inscricoes.equipe_id')
            ->whereNotNull('resultados.pontos_etapa')
            ->select('equipes.id as equipe_id', 'equipes.nome as nome_equipe', DB::raw('SUM(resultados.pontos_etapa) as total_pontos'))
            ->groupBy('equipes.id', 'equipes.nome')
            ->orderByDesc('total_pontos')
            ->get();

        return view('campeonatos.show', compact('campeonato', 'etapas', 'rankingAtletas', 'rankingEquipes'));
    }

    /**
     * Exibe o ranking geral do campeonato (soma de pontos de todas as etapas).
     * Agrupado por percurso e categoria, igual à pontuação do evento.
     */
    public function ranking(Campeonato $campeonato): View
    {
        if ($campeonato->status === 'cancelado') {
            abort(404);
        }

        $campeonato->load(['organizacao']);

        $etapas = Evento::where('campeonato_id', $campeonato->id)
            ->orderBy('data_evento', 'asc')
            ->get();

        $etapasParaRanking = $etapas->map(fn ($e, $i) => ['id' => $e->id, 'numero' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)]);

        $linhas = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')
            ->join('users', 'atletas.user_id', '=', 'users.id')
            ->join('categorias', 'inscricoes.categoria_id', '=', 'categorias.id')
            ->join('percursos', 'categorias.percurso_id', '=', 'percursos.id')
            ->leftJoin('equipes', 'inscricoes.equipe_id', '=', 'equipes.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->whereNotNull('resultados.pontos_etapa')
            ->select(
                'percursos.id as percurso_id',
                'percursos.descricao as percurso_desc',
                'categorias.id as categoria_id',
                'categorias.nome as categoria_nome',
                'categorias.genero as categoria_genero',
                'atletas.id as atleta_id',
                'users.name as nome_atleta',
                'equipes.id as equipe_id',
                'equipes.nome as nome_equipe',
                'eventos.id as evento_id',
                'resultados.pontos_etapa as pontos'
            )
            ->get();

        $categoriasParaFiltro = $linhas->map(function ($r) {
            $gen = $r->categoria_genero ? ucfirst(mb_strtolower($r->categoria_genero)) : '';
            return $r->percurso_desc . ' | ' . $r->categoria_nome . ($gen ? ' - ' . $gen : '');
        })->unique()->sort()->values()->all();

        $etapaIds = $etapas->pluck('id')->all();
        $gruposAtletas = $this->agruparRankingPorPercursoCategoria($linhas, $etapaIds, 'atleta_id', 'nome_atleta', 'atleta_id');

        // Ranking por equipe: totalização de pontos dos atletas inscritos na equipe (uma lista, sem percurso/categoria)
        $linhasEquipes = $linhas->whereNotNull('equipe_id');
        $rankingEquipesFlat = $this->rankingFlatPorEquipe($linhasEquipes, $etapaIds);

        return view('campeonatos.ranking', compact(
            'campeonato', 'etapas', 'etapasParaRanking',
            'gruposAtletas', 'rankingEquipesFlat', 'categoriasParaFiltro'
        ));
    }

    /**
     * Ranking flat por equipe: soma dos pontos de todos os atletas da equipe no campeonato (por etapa e total).
     */
    private function rankingFlatPorEquipe(Collection $linhas, array $etapaIds): array
    {
        $porEquipe = [];
        foreach ($linhas as $r) {
            $equipeId = $r->equipe_id;
            $equipeNome = $r->nome_equipe ?? '—';
            if (!isset($porEquipe[$equipeId])) {
                $porEquipe[$equipeId] = [
                    'id' => $equipeId,
                    'nome' => $equipeNome,
                    'pontos_por_etapa' => array_combine($etapaIds, array_fill(0, count($etapaIds), 0)) ?: [],
                    'total_pontos' => 0,
                ];
            }
            $pts = (int) ($r->pontos ?? 0);
            $porEquipe[$equipeId]['pontos_por_etapa'][$r->evento_id] = ($porEquipe[$equipeId]['pontos_por_etapa'][$r->evento_id] ?? 0) + $pts;
            $porEquipe[$equipeId]['total_pontos'] += $pts;
        }
        uasort($porEquipe, fn ($a, $b) => $b['total_pontos'] <=> $a['total_pontos']);
        return array_values($porEquipe);
    }

    /**
     * Agrupa linhas de resultado por percurso -> categoria -> atleta/equipe, com pontos por etapa e total.
     */
    private function agruparRankingPorPercursoCategoria(Collection $linhas, array $etapaIds, string $idKey, string $nomeKey, string $idField): array
    {
        $agrupado = [];

        foreach ($linhas as $r) {
            $percursoKey = $r->percurso_id ?? 0;
            $percursoDesc = $r->percurso_desc ?? 'Geral';
            $catKey = $r->categoria_id ?? 0;
            $catNome = $r->categoria_nome ?? 'N/A';
            $catGenero = $r->categoria_genero ? ucfirst(mb_strtolower($r->categoria_genero)) : '';
            $catLabel = $catGenero ? $catNome . ' - ' . $catGenero : $catNome;
            $entidadeId = $r->{$idField} ?? null;
            $entidadeNome = $r->{$nomeKey} ?? '—';
            if ($entidadeId === null) {
                continue;
            }
            if (!isset($agrupado[$percursoKey])) {
                $agrupado[$percursoKey] = ['percurso_id' => $percursoKey, 'percurso_desc' => $percursoDesc, 'categorias' => []];
            }
            if (!isset($agrupado[$percursoKey]['categorias'][$catKey])) {
                $agrupado[$percursoKey]['categorias'][$catKey] = [
                    'categoria_id' => $catKey,
                    'categoria_label' => $catLabel,
                    'filtro_value' => $percursoDesc . ' | ' . $catLabel,
                    'atletas' => [],
                ];
            }
            $cat = &$agrupado[$percursoKey]['categorias'][$catKey];
            if (!isset($cat['atletas'][$entidadeId])) {
                $cat['atletas'][$entidadeId] = [
                    'id' => $entidadeId,
                    'nome' => $entidadeNome,
                    'pontos_por_etapa' => array_combine($etapaIds, array_fill(0, count($etapaIds), 0)) ?: [],
                    'total_pontos' => 0,
                ];
            }
            $pts = (int) ($r->pontos ?? 0);
            $cat['atletas'][$entidadeId]['pontos_por_etapa'][$r->evento_id] = ($cat['atletas'][$entidadeId]['pontos_por_etapa'][$r->evento_id] ?? 0) + $pts;
            $cat['atletas'][$entidadeId]['total_pontos'] += $pts;
        }

        foreach (array_keys($agrupado) as $pk) {
            foreach (array_keys($agrupado[$pk]['categorias']) as $ck) {
                $atletas = $agrupado[$pk]['categorias'][$ck]['atletas'];
                uasort($atletas, fn ($a, $b) => $b['total_pontos'] <=> $a['total_pontos']);
                $agrupado[$pk]['categorias'][$ck]['atletas'] = array_values($atletas);
            }
            $agrupado[$pk]['categorias'] = array_values($agrupado[$pk]['categorias']);
        }

        return array_values($agrupado);
    }
}