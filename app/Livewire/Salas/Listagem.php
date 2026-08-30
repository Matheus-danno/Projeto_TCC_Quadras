<?php

namespace App\Livewire\Salas;

use App\Enums\Esporte;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    public string $esporte = '';

    #[Computed]
    public function salas(): Collection
    {
        return Sala::query()
            ->with(['quadra', 'criador', 'participantes'])
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.salas.listagem', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
