<?php

namespace App\Policies;

use App\Models\Sala;
use App\Models\User;

class SalaPolicy
{
    /**
     * Ações de organizador: renomear a sala, aprovar/recusar pedidos de
     * entrada e pagar a diferença para fechar com vagas sobrando.
     */
    public function gerenciar(User $user, Sala $sala): bool
    {
        return $user->id === $sala->criador_id;
    }
}
