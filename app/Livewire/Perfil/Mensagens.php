<?php

namespace App\Livewire\Perfil;

use App\Models\Conversa;
use App\Models\Quadra;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Mensagens extends Component
{
    public ?int $quadraSelecionada = null;

    public string $novaMensagem = '';

    /**
     * Quadras onde o usuário autenticado organizou pelo menos uma sala,
     * ou seja, quadras que ele alugou e pode falar com o dono.
     */
    #[Computed]
    public function quadras()
    {
        $quadras = Quadra::query()
            ->whereHas('salas', fn ($query) => $query->where('criador_id', Auth::id()))
            ->with('dono')
            ->orderBy('nome')
            ->get();

        $conversas = Conversa::whereIn('quadra_id', $quadras->pluck('id'))
            ->where('jogador_id', Auth::id())
            ->with('mensagens')
            ->get()
            ->keyBy('quadra_id');

        return $quadras->each(function (Quadra $quadra) use ($conversas) {
            $quadra->ultimaMensagem = $conversas->get($quadra->id)?->ultimaMensagem();
        });
    }

    #[Computed]
    public function conversaAtual(): ?Conversa
    {
        if ($this->quadraSelecionada === null) {
            return null;
        }

        return Conversa::where('quadra_id', $this->quadraSelecionada)
            ->where('jogador_id', Auth::id())
            ->with('mensagens.user')
            ->first();
    }

    public function selecionarQuadra(int $quadraId): void
    {
        abort_unless($this->quadras->contains('id', $quadraId), 403);

        $this->quadraSelecionada = $quadraId;
        $this->novaMensagem = '';
        $this->resetErrorBag();
    }

    public function voltar(): void
    {
        $this->quadraSelecionada = null;
    }

    public function enviarMensagem(): void
    {
        abort_unless($this->quadras->contains('id', $this->quadraSelecionada), 403);

        $validated = $this->validate([
            'novaMensagem' => ['required', 'string', 'max:500'],
        ], [
            'novaMensagem.required' => 'Escreva uma mensagem antes de enviar.',
            'novaMensagem.max' => 'A mensagem pode ter no máximo 500 caracteres.',
        ]);

        $conversa = Conversa::firstOrCreate([
            'quadra_id' => $this->quadraSelecionada,
            'jogador_id' => Auth::id(),
        ]);

        $conversa->mensagens()->create([
            'user_id' => Auth::id(),
            'texto' => trim($validated['novaMensagem']),
        ]);

        $this->novaMensagem = '';

        unset($this->conversaAtual);
    }

    public function render()
    {
        return view('livewire.perfil.mensagens');
    }
}
