<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado e se o método 'isAdmin()' retorna true.
        if (auth()->check() && auth()->user()->isAdmin()) {
            // Se for um administrador, permite que a requisição continue para a próxima etapa.
            return $next($request);
        }

        // Se não for um administrador, bloqueia o acesso com um erro 403 (Acesso Proibido).
        abort(403, 'Acesso restrito a administradores.');
    }
}
