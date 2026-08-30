<?php

namespace App\Livewire\Perfil;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MeusPedidos extends Component
{
    #[Computed]
    public function pedidos()
    {
        return Auth::user()->pedidos()
            ->with('itens.produto')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.perfil.meus-pedidos');
    }
}
