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
}
