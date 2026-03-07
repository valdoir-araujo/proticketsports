<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventoPolicy
{
    use HandlesAuthorization;

    /**
     * Permite que o super admin aceda a tudo.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Determina se o utilizador pode visualizar o evento.
     * Apenas membros da organização dona do evento podem vê-lo no painel.
     */
    public function view(User $user, Evento $evento): bool
    {
        return $user->organizacoes->contains($evento->organizacao);
    }

    /**
     * Determina se o utilizador pode criar eventos.
     * (Neste caso, a verificação é feita no controller, mas poderia estar aqui)
     */
    public function create(User $user): bool
    {
        return $user->isOrganizador();
    }

    /**
     * Determina se o utilizador pode atualizar o evento.
     */
    public function update(User $user, Evento $evento): bool
    {
        return $user->organizacoes->contains($evento->organizacao);
    }

    /**
     * Determina se o utilizador pode apagar o evento.
     */
    public function delete(User $user, Evento $evento): bool
    {
        return $user->organizacoes->contains($evento->organizacao);
    }
}
