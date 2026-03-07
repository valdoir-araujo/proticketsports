<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsOrganizador
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // NOVA LÓGICA:
        // Verifica se o usuário é Admin (que pode tudo)
        // OU se o usuário (que é atleta) POSSUI alguma organização.
        if ($user && ($user->isAdmin() || $user->organizacoes()->exists())) {
            return $next($request);
        }

        // Se não for nenhum dos dois, bloqueia o acesso.
        abort(403, 'Acesso não autorizado.');
    }
}

