<?php

namespace App\Livewire\Salas;

use App\Models\Sala;
use Livewire\Component;

class Detalhe extends Component
{
    public Sala $sala;

    public function mount(Sala $sala): void
    {
        $this->sala = $sala->load(['quadra.fotos', 'criador', 'participantes']);
    }

    public function render()
    {
        return view('livewire.salas.detalhe');
    }
}
