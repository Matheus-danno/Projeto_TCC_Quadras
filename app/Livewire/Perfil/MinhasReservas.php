<?php

namespace App\Livewire\Perfil;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MinhasReservas extends Component
{
    #[Computed]
    public function futuras()
    {
        return Auth::user()->reservas()
            ->with('quadra')
            ->whereDate('data', '>=', now()->toDateString())
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->get();
    }

    #[Computed]
    public function passadas()
    {
        return Auth::user()->reservas()
            ->with('quadra')
            ->whereDate('data', '<', now()->toDateString())
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->get();
    }

    public function render()
    {
        return view('livewire.perfil.minhas-reservas');
    }
}
