<?php

namespace App\Policies;

use App\Models\Campeonato;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampeonatoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o utilizador pode ver o campeonato.
     * Esta regra será usada para a página de regras de pontuação.
     */
    public function view(User $user, Campeonato $campeonato): bool
    {
        // A lógica é: permitir o acesso se o ID do utilizador logado for o mesmo
        // ID do utilizador que é dono da organização à qual o campeonato pertence.
        return $user->id === $campeonato->organizacao->user_id;
    }

    /**
     * Determina se o utilizador pode atualizar o campeonato.
     */
    public function update(User $user, Campeonato $campeonato): bool
    {
        return $user->id === $campeonato->organizacao->user_id;
    }

    // Adicione outras regras como delete, create, etc., se necessário.
}
