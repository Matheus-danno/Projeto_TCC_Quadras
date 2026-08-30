<?php

namespace App\Livewire\Salas;

use App\Models\AtividadeSala;
use App\Models\Sala;
use Livewire\Component;

class Grupo extends Component
{
    public Sala $sala;

    public ?string $erro = null;

    public string $novaMensagem = '';

    public bool $mensagemPrivada = false;

    public function mount(Sala $sala): void
    {
        abort_unless(auth()->check() && $sala->participantes->contains('id', auth()->id()), 403);

        $this->sala = $sala->load(['quadra', 'criador', 'participantes', 'atividades.user']);

        $this->carregarMensagens();
    }

    public function enviarMensagem(): void
    {
        $validated = $this->validate([
            'novaMensagem' => ['required', 'string', 'max:500'],
        ], [
            'novaMensagem.required' => 'Escreva uma mensagem antes de enviar.',
            'novaMensagem.max' => 'A mensagem pode ter no máximo 500 caracteres.',
        ]);

        $this->sala->mensagens()->create([
            'user_id' => auth()->id(),
            'destinatario_id' => $this->mensagemPrivada ? $this->sala->criador_id : null,
            'texto' => trim($validated['novaMensagem']),
        ]);

        $this->novaMensagem = '';

        $this->carregarMensagens();
    }

    /**
     * Recarrega as mensagens do chat (chamado periodicamente via wire:poll para
     * que os participantes vejam mensagens novas de outros usuários).
     */
    public function atualizarMensagens(): void
    {
        $this->carregarMensagens();
    }

    /**
     * Carrega as mensagens visíveis para o usuário autenticado: as públicas do
     * chat da sala, mais as privadas que ele enviou ou recebeu.
     */
    private function carregarMensagens(): void
    {
        $userId = auth()->id();

        $this->sala->load(['mensagens' => function ($query) use ($userId) {
            $query->where(fn ($sub) => $sub->whereNull('destinatario_id')
                ->orWhere('user_id', $userId)
                ->orWhere('destinatario_id', $userId)
            )->with('user');
        }]);
    }

    public function sairDaSala(): void
    {
        $this->erro = null;

        if (auth()->id() === $this->sala->criador_id && $this->sala->participantes->count() > 1) {
            $this->erro = 'Você é o organizador da sala. Não é possível sair enquanto houver outros jogadores confirmados.';

            return;
        }

        $minutos = $this->sala->minutosParaComeco();

        if ($minutos === null || $minutos < 300) {
            $this->erro = 'Cancelamentos só podem ser feitos até 5h antes do início do jogo.';

            return;
        }

        $this->sala->participantes()->detach(auth()->id());

        AtividadeSala::create([
            'sala_id' => $this->sala->id,
            'user_id' => auth()->id(),
            'descricao' => auth()->user()->name.' saiu da sala',
        ]);

        session()->flash('sala-criada', 'Você saiu da sala.');

        $this->redirect(route('encontre_time'), navigate: false);
    }

    public function render()
    {
        return view('livewire.salas.grupo');
    }
}
