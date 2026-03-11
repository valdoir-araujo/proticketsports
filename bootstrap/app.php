<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ==========================================================
        // REGISTRO DOS MIDDLEWARES DE PERMISSÃO
        // ==========================================================
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'is_organizador' => \App\Http\Middleware\IsOrganizador::class,
        ]);
        // Webhook do Mercado Pago: chamado pelo servidor deles (sem cookie CSRF)
        $middleware->validateCsrfTokens(except: [
            'webhook/mercadopago',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 419 "Sessão expirada" (CSRF inválido): redireciona para login com mensagem amigável (evita erro no mobile)
        $exceptions->stopIgnoring(\Illuminate\Session\TokenMismatchException::class);
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sessão expirada. Atualize a página e tente novamente.'], 419);
            }
            $loginRoute = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
            return redirect($loginRoute)
                ->with('error', 'Sua sessão expirou. Por favor, tente fazer login novamente.')
                ->withInput($request->only('login', 'email'));
        });
    })
    ->withProviders([
        \App\Providers\PaymentServiceProvider::class,
    ])
    ->create();

