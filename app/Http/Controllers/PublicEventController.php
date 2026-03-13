<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class PublicEventController extends Controller
{
    /**
     * Exibe a lista pública de eventos com filtros e paginação.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Constrói a query base
        $query = Evento::query()
                       ->with('cidade.estado')
                       ->where('status', 'publicado')
                       ->where('data_evento', '>=', now())
                       ->orderBy('data_evento', 'asc');

        // Aplica os filtros de busca
        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('modalidade_id')) {
            $query->where('modalidade_id', $request->modalidade_id);
        }
        if ($request->filled('cidade')) {
            $query->whereHas('cidade', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->cidade . '%');
            });
        }
        if ($request->filled('estado')) {
            $query->whereHas('cidade.estado', function ($q) use ($request) {
                $q->where('uf', $request->estado);
            });
        }
        if ($request->filled('data_inicio')) {
            $dataInicio = \Carbon\Carbon::parse($request->data_inicio)->startOfDay();
            $query->where('data_evento', '>=', $dataInicio);
        }

        $eventos = $query->paginate(12)->withQueryString();

        // Lógica AJAX para "Carregar Mais"
        if ($request->ajax()) {
            $html = view('eventos.public._event-list', compact('eventos'))->render();
            return response()->json([
                'html' => $html,
                'has_more' => $eventos->hasMorePages()
            ]);
        }

        // Lista de estados para filtro – cache 30 min (dados quase estáticos)
        $estados = Cache::remember('public_eventos_estados', 1800, fn () => Estado::orderBy('nome')->get());

        return view('eventos.public.index', compact('eventos', 'estados'));
    }

    /**
     * Exibe a página de detalhes de um evento específico.
     */
    public function show(Evento $evento): View
    {
        if ($evento->status !== 'publicado' && auth()->user()?->id !== $evento->organizador_id) {
            abort(404);
        }

        $inscricaoExistente = null;
        $atleta = null;

        if (Auth::check() && Auth::user()->atleta) {
            $atleta = Auth::user()->atleta;
            $inscricaoExistente = Inscricao::where('atleta_id', $atleta->id)
                                              ->where('evento_id', $evento->id)
                                              ->first();
        }

        $evento->load('percursos.categorias', 'cidade.estado', 'eventoContatos');

        return view('eventos.public.show', compact('evento', 'inscricaoExistente', 'atleta'));
    }

    /**
     * Exibe a página dedicada com a lista de inscritos de um evento (com paginação).
     */
    public function showInscritos(Evento $evento): View
    {
        if (!$evento->lista_inscritos_publica) {
            abort(404);
        }

        // Lista de categorias para o filtro (query leve, independente da página).
        $categoriasParaFiltro = \App\Models\Categoria::whereHas('inscricoes', function ($q) use ($evento) {
            $q->where('evento_id', $evento->id)->where('status', '!=', 'cancelada');
        })
            ->with('percurso:id,descricao')
            ->get()
            ->map(function ($cat) {
                if ($cat->percurso) {
                    return $cat->percurso->descricao . ' | ' . $cat->nome . ' - ' . ucfirst($cat->genero ?? '');
                }
                return $cat->nome . ' - ' . ucfirst($cat->genero ?? '');
            })
            ->unique()
            ->sort()
            ->values();

        // Inscrições paginadas (ordenadas por percurso, categoria, nome).
        $inscricoes = Inscricao::where('inscricoes.evento_id', $evento->id)
            ->where('inscricoes.status', '!=', 'cancelada')
            ->has('atleta.user')
            ->with(['atleta.user', 'atleta.cidade.estado', 'equipe', 'categoria.percurso'])
            ->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')
            ->join('users', 'atletas.user_id', '=', 'users.id')
            ->join('categorias', 'inscricoes.categoria_id', '=', 'categorias.id')
            ->join('percursos', 'categorias.percurso_id', '=', 'percursos.id')
            ->orderBy('percursos.id')
            ->orderBy('categorias.id')
            ->orderBy('users.name')
            ->select('inscricoes.*')
            ->paginate(80, ['*'], 'page');

        return view('eventos.public.inscritos', compact('evento', 'inscricoes', 'categoriasParaFiltro'));
    }

    /**
     * Exibe a página pública de resultados de um evento (somente leitura, com paginação na lista).
     */
    public function showResultados(Evento $evento): View
    {
        if ($evento->status !== 'publicado') {
            abort(404);
        }

        // Ranking de equipes (calculado sobre todos os inscritos com resultado).
        $inscricoesParaRanking = Inscricao::query()
            ->where('evento_id', $evento->id)
            ->where('status', 'confirmada')
            ->whereNotNull('equipe_id')
            ->with(['equipe', 'resultado'])
            ->whereHas('resultado')
            ->get();
        $rankingEquipesEtapa = $inscricoesParaRanking
            ->groupBy('equipe_id')
            ->map(function ($inscricoesDaEquipe) {
                $equipe = $inscricoesDaEquipe->first()->equipe;
                if (!$equipe) {
                    return null;
                }
                return [
                    'equipe' => $equipe,
                    'pontos_totais' => $inscricoesDaEquipe->sum(fn ($i) => $i->resultado?->pontos_etapa ?? 0),
                ];
            })
            ->filter()
            ->sortByDesc('pontos_totais')
            ->values();

        // Lista de categorias para o filtro.
        $categoriasParaFiltro = \App\Models\Categoria::whereHas('inscricoes', function ($q) use ($evento) {
            $q->where('evento_id', $evento->id)->where('status', 'confirmada');
        })
            ->with('percurso:id,descricao')
            ->get()
            ->map(function ($cat) {
                if ($cat->percurso) {
                    return $cat->percurso->descricao . ' | ' . $cat->nome . ' - ' . ucfirst($cat->genero ?? '');
                }
                return $cat->nome . ' - ' . ucfirst($cat->genero ?? '');
            })
            ->unique()
            ->sort()
            ->values();

        // Lista de inscrições paginada (para a tabela de atletas/resultados).
        $inscricoes = Inscricao::query()
            ->where('inscricoes.evento_id', $evento->id)
            ->where('inscricoes.status', 'confirmada')
            ->with(['atleta.user', 'atleta.equipe', 'equipe', 'categoria.percurso', 'resultado'])
            ->join('categorias', 'inscricoes.categoria_id', '=', 'categorias.id')
            ->join('percursos', 'categorias.percurso_id', '=', 'percursos.id')
            ->orderBy('percursos.id')
            ->orderBy('categorias.id')
            ->select('inscricoes.*')
            ->paginate(80, ['*'], 'page');

        $totalComResultado = \App\Models\Resultado::whereIn('inscricao_id', Inscricao::where('evento_id', $evento->id)->where('status', 'confirmada')->pluck('id'))->count();

        return view('eventos.public.resultados', compact('evento', 'inscricoes', 'rankingEquipesEtapa', 'categoriasParaFiltro', 'totalComResultado'));
    }
}

