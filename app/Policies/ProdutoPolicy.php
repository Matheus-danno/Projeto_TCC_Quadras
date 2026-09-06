<?php

namespace App\Policies;

use App\Models\Produto;
use App\Models\User;

class ProdutoPolicy
{
    public function view(User $user, Produto $produto): bool
    {
        return $user->id === $produto->dono_id;
    }

    public function update(User $user, Produto $produto): bool
    {
        return $user->id === $produto->dono_id;
    }

    public function delete(User $user, Produto $produto): bool
    {
        return $this->update($user, $produto);
    }
}
