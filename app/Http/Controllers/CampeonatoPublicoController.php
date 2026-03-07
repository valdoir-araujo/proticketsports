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

        // 2. Lógica para o Ranking de Atletas
        // TODO: Substitua esta query fictícia pela sua lógica real de cálculo de pontos
        // Geralmente envolve juntar as tabelas de inscricoes, resultados e atletas
        $rankingAtletas = collect([]); 
        
        /* Exemplo de como poderia ser uma query real para o ranking:
        $rankingAtletas = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')
            ->join('users', 'atletas.user_id', '=', 'users.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->select('users.name as nome_atleta', DB::raw('SUM(resultados.pontos) as total_pontos'))
            ->groupBy('users.name')
            ->orderByDesc('total_pontos')
            ->get();
        */

        // 3. Lógica para o Ranking de Equipas
        // TODO: Substitua pela sua lógica real
        $rankingEquipes = collect([]);

        /* Exemplo de query para equipas:
        $rankingEquipes = DB::table('resultados')
            ->join('inscricoes', 'resultados.inscricao_id', '=', 'inscricoes.id')
            ->join('equipes', 'inscricoes.equipe_id', '=', 'equipes.id')
            ->join('eventos', 'inscricoes.evento_id', '=', 'eventos.id')
            ->where('eventos.campeonato_id', $campeonato->id)
            ->select('equipes.nome as nome_equipe', DB::raw('SUM(resultados.pontos) as total_pontos'))
            ->groupBy('equipes.nome')
            ->orderByDesc('total_pontos')
            ->get();
        */

        return view('campeonatos.show', compact('campeonato', 'etapas', 'rankingAtletas', 'rankingEquipes'));
    }
}