<?php

namespace App\Policies;

use App\Models\Quadra;
use App\Models\User;

class QuadraPolicy
{
    public function view(User $user, Quadra $quadra): bool
    {
        return $user->id === $quadra->dono_id;
    }

    public function update(User $user, Quadra $quadra): bool
    {
        return $user->id === $quadra->dono_id;
    }

    public function delete(User $user, Quadra $quadra): bool
    {
        return $this->update($user, $quadra);
    }
}
