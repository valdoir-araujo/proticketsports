<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Permission;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Exemplo: 'App\Models\Inscricao' => 'App\Policies\InscricaoPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Esta linha é OBRIGATÓRIA para que o array $policies acima funcione
        $this->registerPolicies();

        // --- LÓGICA DE ACL (ADICIONADA) ---
        
        try {
            // Verifica se a tabela existe para evitar erros antes da migração
            if (Schema::hasTable('permissions')) {
                // Define dinamicamente um Gate para cada permissão no banco
                // Ex: @can('gerenciar_financeiro')
                $permissions = Permission::all();

                foreach ($permissions as $permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                }
            }
        } catch (\Exception $e) {
            // Ignora erros de conexão durante o boot (ex: composer install)
        }

        // Gate especial para Super Admin (sempre passa em tudo)
        // Isso permite que o Admin acesse qualquer área sem precisar marcar os checkboxes
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}