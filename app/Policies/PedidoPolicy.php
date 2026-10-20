<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function view(User $user, Pedido $pedido): bool
    {
        return $user->id === $pedido->dono_id;
    }

    public function update(User $user, Pedido $pedido): bool
    {
        return $user->id === $pedido->dono_id;
    }

    /**
     * Tela de confirmação logo após a compra: só quem comprou.
     */
    public function verConfirmacao(User $user, Pedido $pedido): bool
    {
        return $user->id === $pedido->user_id;
    }

    /**
     * Nota de compra imprimível: quem comprou e o dono que vendeu.
     */
    public function verNota(User $user, Pedido $pedido): bool
    {
        return in_array($user->id, [$pedido->user_id, $pedido->dono_id], true);
    }
}
