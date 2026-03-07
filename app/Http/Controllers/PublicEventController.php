<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Estado;
use Illuminate\Http\Request;
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

        $estados = Estado::orderBy('nome')->get();

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

        $evento->load('percursos.categorias', 'cidade.estado');

        return view('eventos.public.show', compact('evento', 'inscricaoExistente', 'atleta'));
    }

    /**
     * Exibe a página dedicada com a lista de inscritos de um evento.
     */
    public function showInscritos(Evento $evento): View
    {
        if (!$evento->lista_inscritos_publica) {
            abort(404);
        }

        // 1. Busca todas as inscrições válidas.
        $inscricoes = Inscricao::where('evento_id', $evento->id)
            ->where('status', '!=', 'cancelada')
            ->has('atleta.user') 
            ->with(['atleta.user', 'atleta.cidade.estado', 'equipe', 'categoria.percurso'])
            ->get();

        // --- LÓGICA DE ORDENAÇÃO AJUSTADA ---
        // A lista é agora ordenada pelo ID do percurso, depois pelo ID da categoria,
        // e finalmente pelo nome do atleta.
        $inscricoes = $inscricoes->sortBy([
            'categoria.percurso_id',
            'categoria_id',
            'atleta.user.name',
        ])->values();

        // 2. Prepara a lista de categorias para o dropdown de filtro.
        $categoriasParaFiltro = $inscricoes->map(function ($inscricao) {
            if ($inscricao->categoria && $inscricao->categoria->percurso) {
                return $inscricao->categoria->percurso->descricao . ' | ' . $inscricao->categoria->nome . ' - ' . ucfirst($inscricao->categoria->genero);
            }
            return null;
        })->filter()->unique()->sort()->values();

        // 3. Envia as variáveis prontas para a view.
        return view('eventos.public.inscritos', compact('evento', 'inscricoes', 'categoriasParaFiltro'));
    }
    
}

