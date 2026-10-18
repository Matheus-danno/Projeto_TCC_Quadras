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
        abort_unless($usuario->role === UserRole::DonoQuadra, 404);

        $this->usuario = $usuario;
    }

    /**
     * Reservas recebidas nas quadras do dono, da mais recente para a mais
     * antiga. Jogadores não têm página própria aqui — o histórico deles fica
     * visível pro dono da quadra em Configurações.
     */
    #[Computed]
    public function reservas(): Collection
    {
        return Reserva::query()
            ->whereHas('quadra', fn ($q) => $q->where('dono_id', $this->usuario->id))
            ->with('quadra')
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->limit(50)
            ->get();
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
