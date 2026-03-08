<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Correção para tamanho de string em bancos de dados mais antigos
        Schema::defaultStringLength(191);

        // Regra global de senha: mínimo 5 caracteres (usado em registro, alteração e redefinição de senha)
        Password::defaults(function () {
            return Password::min(5);
        });

        // --- CORREÇÃO DEFINITIVA PARA HTTPS (HOSTINGER/CLOUDFLARE) ---
        
        // 1. Força a geração de URLs com https://
        //URL::forceScheme('https');

        // 2. "Engana" o Laravel para ele achar que a requisição original já veio segura.
        // Isso corrige o carregamento de CSS/JS (Mixed Content) no iPhone/Chrome.
       // if(isset($this->app['request'])) {
       //     $this->app['request']->server->set('HTTPS', 'on');
       // }
    }
}