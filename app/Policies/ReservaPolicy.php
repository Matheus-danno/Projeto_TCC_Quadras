<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\User;

class ReservaPolicy
{
    public function update(User $user, Reserva $reserva): bool
    {
        return $user->id === $reserva->quadra->dono_id;
    }

    /**
     * Tela de confirmação da reserva: só o jogador que reservou.
     */
    public function verConfirmacao(User $user, Reserva $reserva): bool
    {
        return $user->id === $reserva->user_id;
    }
}
