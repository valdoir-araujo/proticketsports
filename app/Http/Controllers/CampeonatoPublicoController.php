<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
     * Inclui etapas e pontos por etapa para montar tabela com colunas por etapa.
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

        $pontosAtletaPorEtapa = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->whereNotNull('resultados.pontos_etapa')
            ->select('atletas.id as atleta_id', 'eventos.id as evento_id', DB::raw('SUM(resultados.pontos_etapa) as pontos'))
            ->groupBy('atletas.id', 'eventos.id')
            ->get()
            ->groupBy('atleta_id')
            ->map(fn ($g) => $g->keyBy('evento_id')->map(fn ($r) => (int) $r->pontos));

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

        $pontosEquipePorEtapa = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('equipes', 'inscricoes.equipe_id', '=', 'equipes.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->whereNotNull('inscricoes.equipe_id')
            ->whereNotNull('resultados.pontos_etapa')
            ->select('equipes.id as equipe_id', 'eventos.id as evento_id', DB::raw('SUM(resultados.pontos_etapa) as pontos'))
            ->groupBy('equipes.id', 'eventos.id')
            ->get()
            ->groupBy('equipe_id')
            ->map(fn ($g) => $g->keyBy('evento_id')->map(fn ($r) => (int) $r->pontos));

        return view('campeonatos.ranking', compact('campeonato', 'etapas', 'rankingAtletas', 'rankingEquipes', 'pontosAtletaPorEtapa', 'pontosEquipePorEtapa'));
    }
}