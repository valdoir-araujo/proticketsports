<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // 1. Se for Admin (Super Usuário), vai direto para o painel administrativo.
        // Verifica todas as possibilidades (método do model, role ou tipo_usuario).
        if ($user->isAdmin() || $user->role === 'admin' || $user->tipo_usuario === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // 2. Para todos os outros (Atletas e Organizadores), enviamos para o Painel do Atleta.
        // O Organizador terá um botão lá dentro para acessar a área de gestão.
        return redirect()->route('atleta.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}