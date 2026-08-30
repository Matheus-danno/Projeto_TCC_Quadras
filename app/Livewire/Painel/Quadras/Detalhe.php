<?php

namespace App\Livewire\Painel\Quadras;

use App\Models\Quadra;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Detalhe extends Component
{
    public Quadra $quadra;

    public function mount(Quadra $quadra): void
    {
        $this->authorize('view', $quadra);

        $this->quadra = $quadra;
    }

    #[Computed]
    public function reservasRecentes(): Collection
    {
        return Reserva::query()
            ->where('quadra_id', $this->quadra->id)
            ->with('user')
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.painel.quadras.detalhe');
    }
}
