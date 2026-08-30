<?php

namespace App\Livewire\Perfil;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SaldoCreditos extends Component
{
    #[On('creditos-atualizados')]
    public function atualizar(): void
    {
        // Renderiza de novo lendo o valor mais recente do banco.
    }

    public function render()
    {
        return view('livewire.perfil.saldo-creditos', [
            'saldo' => Auth::user()->fresh()->saldo_creditos,
        ]);
    }
}
