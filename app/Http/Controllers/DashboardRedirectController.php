<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class DashboardRedirectController extends Controller
{
    /**
     * Redireciona o usuário para o painel correto com base no seu perfil.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 1. Garante dados frescos do banco para evitar cache de sessão antigo
        $user = Auth::user()->fresh();

        // --------------------------------------------------------
        // REGRA 1: ADMIN (PRIORIDADE MÁXIMA)
        // --------------------------------------------------------
        // Verifica todas as possibilidades de ser Admin.
        if (
            $user->id === 1 || 
            $user->email === 'admin@proticketsports.com.br' ||
            $user->role === 'admin' ||
            $user->tipo_usuario === 'admin' ||
            $user->isAdmin()
        ) {
            return redirect()->route('admin.dashboard');
        }

        // --------------------------------------------------------
        // REGRA 2: ORGANIZADOR (GESTÃO)
        // --------------------------------------------------------
        // Se for organizador (role explícita ou possui organizações), vai para a gestão.
        if (
            $user->role === 'organizador' || 
            $user->tipo_usuario === 'organizador' ||
            $user->isOrganizador() ||
            $user->organizacoes()->exists()
        ) {
            return redirect()->route('organizador.dashboard');
        }

        // --------------------------------------------------------
        // REGRA 3: ATLETA (PADRÃO / FALLBACK)
        // --------------------------------------------------------
        // Se não for Admin nem Organizador, manda para a área do atleta.
        return redirect()->route('atleta.dashboard');
    }
}