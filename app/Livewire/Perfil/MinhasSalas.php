<?php

namespace App\Livewire\Perfil;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MinhasSalas extends Component
{
    /**
     * Salas futuras, incluindo as que ainda não têm data/horário definidos
     * (horário a combinar entre os participantes).
     */
    #[Computed]
    public function futuras()
    {
        return Auth::user()->salas()
            ->with(['quadra', 'criador', 'participantes'])
            ->where(fn ($query) => $query->whereNull('data')->orWhereDate('data', '>=', now()->toDateString()))
            ->orderByRaw('data IS NULL')
            ->orderBy('data')
            ->orderBy('horario_inicio')
            ->get();
    }

    #[Computed]
    public function passadas()
    {
        return Auth::user()->salas()
            ->with(['quadra', 'criador', 'participantes'])
            ->whereDate('data', '<', now()->toDateString())
            ->orderByDesc('data')
            ->orderByDesc('horario_inicio')
            ->get();
    }

    public function render()
    {
        return view('livewire.perfil.minhas-salas');
    }
}
