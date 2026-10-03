<?php

namespace App\Livewire\Salas;

use App\Models\AtividadeSala;
use App\Models\PedidoParticipacao;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Detalhe extends Component
{
    public Sala $sala;

    public array $erros = [];

    public ?string $mensagemPendente = null;

    public bool $mostrarTodosParticipantes = false;

    public bool $editandoNome = false;

    public string $novoNome = '';

    public function mount(Sala $sala): void
    {
        $this->sala = $sala->load(['quadra.fotos', 'criador.avaliacoesRecebidas', 'atividades.user']);
    }

    public function iniciarEdicaoNome(): void
    {
        abort_unless(auth()->id() === $this->sala->criador_id, 403);

        $this->novoNome = $this->sala->nome;
        $this->editandoNome = true;
    }

    public function cancelarEdicaoNome(): void
    {
        $this->editandoNome = false;
    }

    public function salvarNome(): void
    {
        abort_unless(auth()->id() === $this->sala->criador_id, 403);

        $validated = $this->validate([
            'novoNome' => ['required', 'string', 'max:255'],
        ]);

        $this->sala->update(['nome' => trim($validated['novoNome'])]);

        AtividadeSala::create([
            'sala_id' => $this->sala->id,
            'user_id' => auth()->id(),
            'descricao' => 'Nome da sala alterado para "'.$this->sala->nome.'"',
        ]);

        $this->sala->load('atividades.user');
        $this->editandoNome = false;
    }

    #[Computed]
    public function participantes(): Collection
    {
        return $this->sala->participantes()->with('avaliacoesRecebidas')->get();
    }

    #[Computed]
    public function meuPedido(): ?PedidoParticipacao
    {
        if (! auth()->check()) {
            return null;
        }

        return $this->sala->pedidosParticipacao()->where('user_id', auth()->id())->first();
    }

    #[Computed]
    public function pedidosPendentes(): Collection
    {
        if (! auth()->check() || auth()->id() !== $this->sala->criador_id) {
            return collect();
        }

        return $this->sala->pedidosPendentes()->with('user')->get();
    }

    public function entrar()
    {
        unset($this->erros['entrar']);
        $this->mensagemPendente = null;

        if (! auth()->check()) {
            $this->dispatch('login-necessario', mensagem: 'Você precisa entrar para participar de uma sala.');

            return null;
        }

        if ($this->sala->aprovacao === \App\Enums\Aprovacao::Automatica) {
            return $this->redirect(route('salas.pagamento', $this->sala), navigate: false);
        }

        $resultado = $this->sala->entrarComo(auth()->user());

        if ($resultado['pendente']) {
            $this->mensagemPendente = $resultado['mensagem'];
            unset($this->meuPedido);

            return null;
        }

        if (! $resultado['sucesso']) {
            $this->erros['entrar'] = $resultado['mensagem'];

            return null;
        }

        unset($this->participantes);

        return null;
    }

    public function aprovarPedido(int $pedidoId): void
    {
        abort_unless(auth()->id() === $this->sala->criador_id, 403);

        $pedido = $this->sala->pedidosParticipacao()->findOrFail($pedidoId);

        $this->sala->aprovarPedido($pedido);

        unset($this->pedidosPendentes, $this->participantes);
    }

    public function recusarPedido(int $pedidoId): void
    {
        abort_unless(auth()->id() === $this->sala->criador_id, 403);

        $pedido = $this->sala->pedidosParticipacao()->findOrFail($pedidoId);

        $this->sala->recusarPedido($pedido);

        unset($this->pedidosPendentes);
    }

    public function render()
    {
        return view('livewire.salas.detalhe');
    }
}
