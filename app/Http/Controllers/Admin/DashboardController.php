<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Exibe a página principal do painel administrativo com as estatísticas globais.
     */
    public function index(): View
    {
        // ==========================================================
        // LÓGICA PARA BUSCAR AS ESTATÍSTICAS GLOBAIS
        // ==========================================================

        // Conta o total de usuários cadastrados na plataforma.
        $totalUsuarios = User::count();

        // Conta o total de organizações criadas.
        $totalOrganizacoes = Organizacao::count();

        // Conta quantos eventos estão com o status 'publicado'.
        $totalEventosAtivos = Evento::where('status', 'publicado')->count();

        // Calcula a soma de todas as inscrições confirmadas na plataforma.
        $faturamentoGlobal = Inscricao::where('status', 'confirmada')->sum('valor_pago');

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalOrganizacoes',
            'totalEventosAtivos',
            'faturamentoGlobal'
        ));
    }
}

