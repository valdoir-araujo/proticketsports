<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inscricao;
use Illuminate\Support\Facades\Log;

class AtletaController extends Controller
{
    /**
     * Dashboard Principal do Atleta (Hub).
     */
    public function index()
    {
        $user = Auth::user();
        
        // Garante que o usuário tem perfil de atleta criado
        // Se não tiver, redireciona para criar o perfil
        if (!$user->atleta) {
             return redirect()->route('profile.edit')->with('warning', 'Por favor, complete seu perfil de atleta primeiro.');
        }

        // 1. Busca Inscrições (Paginadas)
        $inscricoes = Inscricao::where('atleta_id', $user->atleta->id)
            ->with(['evento', 'categoria', 'equipe', 'resultado']) 
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // 2. Busca Equipas do Atleta
        // O try-catch aqui é opcional agora que a tabela existe, mas mantemos o código limpo
        $equipes = $user->equipes()->orderBy('nome')->get();

        // 3. Verifica se é Organizador para mostrar o card de acesso
        $isOrganizador = $user->isOrganizador() || $user->organizacoes()->exists();

        // Retorna a view do Dashboard
        return view('atleta.dashboard', compact('inscricoes', 'equipes', 'isOrganizador', 'user'));
    }
}