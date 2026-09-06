<?php

namespace App\Livewire\Painel;

use App\Models\Conversa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Mensagens extends Component
{
    public ?int $conversaSelecionada = null;

    public string $novaMensagem = '';

    public string $busca = '';

    /**
     * Conversas de jogadores com as quadras deste dono, da mais recente para a mais antiga.
     */
    #[Computed]
    public function conversas()
    {
        return Conversa::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', Auth::id()))
            ->when($this->busca, function ($query) {
                $termo = '%'.$this->busca.'%';

                $query->where(function ($query) use ($termo) {
                    $query->whereHas('jogador', fn ($query) => $query->where('name', 'like', $termo))
                        ->orWhereHas('quadra', fn ($query) => $query->where('nome', 'like', $termo));
                });
            })
            ->with(['quadra', 'jogador', 'mensagens'])
            ->get()
            ->sortByDesc(fn (Conversa $conversa) => $conversa->ultimaMensagem()?->created_at)
            ->values();
    }

    /**
     * Marca as mensagens do jogador como lidas sempre que a conversa é
     * exibida (na seleção inicial e a cada wire:poll da tela aberta).
     */
    #[Computed]
    public function conversaAtual(): ?Conversa
    {
        if ($this->conversaSelecionada === null) {
            return null;
        }

        $conversa = Conversa::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', Auth::id()))
            ->with(['quadra', 'jogador', 'mensagens.user'])
            ->find($this->conversaSelecionada);

        $conversa?->marcarComoLidaPara(Auth::id());

        return $conversa;
    }

    public function selecionarConversa(int $conversaId): void
    {
        $conversa = Conversa::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', Auth::id()))
            ->findOrFail($conversaId);

        $this->conversaSelecionada = $conversa->id;
        $this->novaMensagem = '';
        $this->resetErrorBag();

        unset($this->conversas);
    }

    public function voltar(): void
    {
        $this->conversaSelecionada = null;
    }

    public function enviarMensagem(): void
    {
        $conversa = Conversa::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', Auth::id()))
            ->findOrFail($this->conversaSelecionada);

        $validated = $this->validate([
            'novaMensagem' => ['required', 'string', 'max:500'],
        ], [
            'novaMensagem.required' => 'Escreva uma mensagem antes de enviar.',
            'novaMensagem.max' => 'A mensagem pode ter no máximo 500 caracteres.',
        ]);

        $conversa->mensagens()->create([
            'user_id' => Auth::id(),
            'texto' => trim($validated['novaMensagem']),
        ]);

        $this->novaMensagem = '';

        unset($this->conversaAtual, $this->conversas);
    }

    public function render()
    {
        return view('livewire.painel.mensagens');
    }
}
