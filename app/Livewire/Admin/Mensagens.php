<?php

namespace App\Livewire\Admin;

use App\Models\Conversa;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Mensagens extends Component
{
    public ?int $conversaSelecionada = null;

    /**
     * Todas as conversas entre jogadores organizadores e donos de quadra,
     * da mais recente para a mais antiga.
     */
    #[Computed]
    public function conversas()
    {
        return Conversa::query()
            ->with(['quadra.dono', 'jogador', 'mensagens'])
            ->get()
            ->sortByDesc(fn (Conversa $conversa) => $conversa->ultimaMensagem()?->created_at)
            ->values();
    }

    #[Computed]
    public function conversaAtual(): ?Conversa
    {
        if ($this->conversaSelecionada === null) {
            return null;
        }

        return Conversa::query()
            ->with(['quadra.dono', 'jogador', 'mensagens.user'])
            ->find($this->conversaSelecionada);
    }

    public function selecionarConversa(int $conversaId): void
    {
        Conversa::findOrFail($conversaId);

        $this->conversaSelecionada = $conversaId;
    }

    public function voltar(): void
    {
        $this->conversaSelecionada = null;
    }

    public function render()
    {
        return view('livewire.admin.mensagens');
    }
}
