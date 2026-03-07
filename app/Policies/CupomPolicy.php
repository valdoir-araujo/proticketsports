<?php

namespace App\Policies;

use App\Models\Cupom;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CupomPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode criar um cupom para o evento.
     */
    public function create(User $user, Evento $evento)
    {
        if (!$evento->organizacao) {
            return Response::deny('O evento não está associado a uma organização.');
        }
        
        return $user->organizacoes->contains($evento->organizacao)
            ? Response::allow()
            : Response::deny('Você não tem permissão para criar cupons para este evento.');
    }

    /**
     * Determina se o usuário pode atualizar o cupom.
     */
    public function update(User $user, Cupom $cupom)
    {
        // 1. Verifica se o usuário tem alguma organização associada a ele.
        if ($user->organizacoes->isEmpty()) {
            return Response::deny('Seu usuário não está associado a nenhuma organização.');
        }

        // 2. Garante que a cadeia de relacionamentos (Cupom -> Evento -> Organizacao) é válida.
        if (!$cupom->evento || !$cupom->evento->organizacao) {
            return Response::deny('Este cupom não está vinculado a um evento ou organização válida.');
        }
        
        // 3. Compara se a organização do cupom está na lista de organizações do usuário.
        $organizacaoDoCupom = $cupom->evento->organizacao;
        
        $possuiPermissao = $user->organizacoes->contains($organizacaoDoCupom);

        return $possuiPermissao
            ? Response::allow()
            : Response::deny('Você não tem permissão para gerenciar cupons desta organização específica.');
    }

    /**
     * Determina se o usuário pode deletar o cupom.
     */
    public function delete(User $user, Cupom $cupom)
    {
        // Reutiliza a mesma lógica segura da atualização
        return $this->update($user, $cupom);
    }
}

