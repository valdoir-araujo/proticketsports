<?php

namespace App\Policies;

use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizacaoPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organizacao $organizacao): bool
    {
        // Um utilizador pode ver uma organização se ele for o dono (owner)
        // ou se for um membro da mesma.
        return $user->id === $organizacao->user_id || $organizacao->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Qualquer utilizador autenticado pode tentar criar uma organização.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organizacao $organizacao): bool
    {
        // Apenas o dono da organização pode atualizá-la.
        // Poderia ser adicionada lógica para permitir que administradores também o façam.
        return $user->id === $organizacao->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organizacao $organizacao): bool
    {
        // Apenas o dono pode apagar a organização.
        return $user->id === $organizacao->user_id;
    }
}
