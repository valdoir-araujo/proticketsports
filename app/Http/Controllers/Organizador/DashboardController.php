<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organizacao;
use App\Models\Inscricao;

class DashboardController extends Controller
{
    /**
     * Exibe a lista de organizações para seleção (Método que faltava).
     * Rota: /organizador (name: organizador.index)
     */
    public function selecaoOrganizacao()
    {
        $organizador = Auth::user();
        
        // Carrega as organizações com dados de localização
        $organizacoes = $organizador->organizacoes()->with(['cidade.estado'])->get();

        // Se não tiver nenhuma, manda criar
        if ($organizacoes->isEmpty()) {
            return redirect()->route('organizador.organizacao.create')
                ->with('info', 'Para começar, você precisa criar sua primeira organização.');
        }

        // Se tiver apenas uma, redireciona direto para o dashboard dela
        if ($organizacoes->count() === 1) {
            return redirect()->route('organizador.dashboard', ['org_id' => $organizacoes->first()->id]);
        }

        // Se tiver várias, mostra a lista (View criada anteriormente: resources/views/organizador/index.blade.php)
        return view('organizador.index', compact('organizacoes'));
    }

    /**
     * Exibe o dashboard principal da organização.
     * Rota: /organizador/dashboard (name: organizador.dashboard)
     */
    public function index(Request $request)
    {
        $organizador = Auth::user();
        $organizacoes = $organizador->organizacoes()->get();

        // 1. Validação básica de existência
        if ($organizacoes->isEmpty()) {
            return redirect()->route('organizador.organizacao.create');
        }

        // 2. Identifica qual organização exibir
        $orgId = $request->query('org_id');
        $organizacao = null;

        if ($orgId) {
            // Busca na coleção para garantir segurança (pertence ao usuário)
            $organizacao = $organizacoes->firstWhere('id', $orgId);
        }

        // 3. Fallback: Se não veio ID ou ID inválido
        if (!$organizacao) {
            if ($organizacoes->count() === 1) {
                $organizacao = $organizacoes->first();
            } else {
                // Se tem várias e tentou acessar sem ID, manda escolher
                return redirect()->route('organizador.index');
            }
        }

        // --- EXIBIR DASHBOARD ---
        return $this->showOrganizationDashboard($organizacao, $organizador);
    }

    /**
     * Método auxiliar para carregar dados e exibir o dashboard específico.
     * Mantida a lógica original de estatísticas.
     */
    private function showOrganizationDashboard(Organizacao $organizacao, $user)
    {
        // --- ESTATÍSTICAS GERAIS ---
        $totalCampeonatos = $organizacao->campeonatos()->count();
        $totalEventos = $organizacao->eventos()->count();
        
        // Ids dos eventos desta organização
        $eventoIds = $organizacao->eventos()->pluck('id');
        
        // Contagem de inscritos e financeiro
        $totalInscritos = Inscricao::whereIn('evento_id', $eventoIds)->count();
        
        $totalInscritosConfirmados = Inscricao::whereIn('evento_id', $eventoIds)
            ->where('status', 'confirmada')
            ->count();
            
        $faturamento = Inscricao::whereIn('evento_id', $eventoIds)
            ->where('status', 'confirmada')
            ->sum('valor_pago');

        // --- LISTAGENS ---
        
        // Campeonatos Ativos
        $campeonatosAtivos = $organizacao->campeonatos()
            ->where('status', 'ativo')
            ->withCount('eventos')
            ->latest('ano')
            ->take(5)
            ->get();

        // Eventos Avulsos (Sem campeonato, futuros)
        $eventosAvulsos = $organizacao->eventos()
            ->whereNull('campeonato_id')
            ->where('data_evento', '>=', now())
            ->orderBy('data_evento', 'asc')
            ->take(5)
            ->get();

        return view('organizador.dashboard', compact(
            'user', 
            'organizacao', 
            'totalCampeonatos', 
            'totalEventos', 
            'totalInscritos', 
            'totalInscritosConfirmados',
            'faturamento',
            'campeonatosAtivos',
            'eventosAvulsos'
        ))->with('organizador', $user); 
    }
}