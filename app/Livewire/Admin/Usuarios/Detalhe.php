<?php

namespace App\Livewire\Admin\Usuarios;

use App\Enums\UserRole;
use App\Models\Reserva;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Detalhe extends Component
{
    use InteractsWithComponents;

    public User $usuario;

    public function mount(User $usuario): void
    {
        abort_if($usuario->role === UserRole::Admin, 404);

        $this->usuario = $usuario;
    }

    /**
     * Reservas feitas pelo usuário (como cliente) ou recebidas nas quadras
     * dele (quando é dono), da mais recente para a mais antiga.
     */
    #[Computed]
    public function reservas(): Collection
    {
        $query = $this->usuario->role === UserRole::DonoQuadra
            ? Reserva::query()->whereHas('quadra', fn ($q) => $q->where('dono_id', $this->usuario->id))
            : Reserva::query()->where('user_id', $this->usuario->id);

        return $query->with('quadra')->orderByDesc('data')->orderByDesc('hora_inicio')->limit(50)->get();
    }

    public function bloquear(): void
    {
        $this->usuario->update(['bloqueado_em' => now()]);

        $this->toast(__('Usuário bloqueado.'), variant: 'success');
    }

    public function desbloquear(): void
    {
        $this->usuario->update(['bloqueado_em' => null]);

        $this->toast(__('Usuário desbloqueado.'), variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.usuarios.detalhe');
    }
}
