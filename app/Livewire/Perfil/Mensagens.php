<?php

namespace App\Livewire\Perfil;

use App\Models\Conversa;
use App\Models\Pedido;
use App\Models\Quadra;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Mensagens extends Component
{
    public ?int $quadraSelecionada = null;

    public ?int $pedidoSelecionado = null;

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
            $conversa = $conversas->get($quadra->id);

            $quadra->ultimaMensagem = $conversa?->ultimaMensagem();
            $quadra->naoLidas = $conversa?->mensagensNaoLidasPara(Auth::id()) ?? 0;
        });
    }

    /**
     * Pedidos feitos na loja pelo jogador autenticado, com quem ele pode
     * falar sobre o produto diretamente com o dono da loja.
     */
    #[Computed]
    public function pedidos()
    {
        $pedidos = Pedido::query()
            ->where('user_id', Auth::id())
            ->with(['dono', 'itens.produto'])
            ->latest()
            ->get();

        $conversas = Conversa::whereIn('pedido_id', $pedidos->pluck('id'))
            ->where('jogador_id', Auth::id())
            ->with('mensagens')
            ->get()
            ->keyBy('pedido_id');

        return $pedidos->each(function (Pedido $pedido) use ($conversas) {
            $conversa = $conversas->get($pedido->id);

            $pedido->ultimaMensagem = $conversa?->ultimaMensagem();
            $pedido->naoLidas = $conversa?->mensagensNaoLidasPara(Auth::id()) ?? 0;
        });
    }

    /**
     * Marca as mensagens da outra parte como lidas sempre que a conversa é
     * exibida (na seleção inicial e a cada wire:poll da tela aberta).
     */
    #[Computed]
    public function conversaAtual(): ?Conversa
    {
        if ($this->quadraSelecionada !== null) {
            $conversa = Conversa::where('quadra_id', $this->quadraSelecionada)
                ->where('jogador_id', Auth::id())
                ->with('mensagens.user')
                ->first();
        } elseif ($this->pedidoSelecionado !== null) {
            $conversa = Conversa::where('pedido_id', $this->pedidoSelecionado)
                ->where('jogador_id', Auth::id())
                ->with('mensagens.user')
                ->first();
        } else {
            return null;
        }

        $conversa?->marcarComoLidaPara(Auth::id());

        return $conversa;
    }

    public function selecionarQuadra(int $quadraId): void
    {
        abort_unless($this->quadras->contains('id', $quadraId), 403);

        $this->quadraSelecionada = $quadraId;
        $this->pedidoSelecionado = null;
        $this->novaMensagem = '';
        $this->resetErrorBag();
    }

    public function selecionarPedido(int $pedidoId): void
    {
        abort_unless($this->pedidos->contains('id', $pedidoId), 403);

        $this->pedidoSelecionado = $pedidoId;
        $this->quadraSelecionada = null;
        $this->novaMensagem = '';
        $this->resetErrorBag();
    }

    public function voltar(): void
    {
        $this->quadraSelecionada = null;
        $this->pedidoSelecionado = null;
    }

    public function enviarMensagem(): void
    {
        if ($this->quadraSelecionada !== null) {
            abort_unless($this->quadras->contains('id', $this->quadraSelecionada), 403);
        } elseif ($this->pedidoSelecionado !== null) {
            abort_unless($this->pedidos->contains('id', $this->pedidoSelecionado), 403);
        } else {
            abort(403);
        }

        $validated = $this->validate([
            'novaMensagem' => ['required', 'string', 'max:500'],
        ], [
            'novaMensagem.required' => 'Escreva uma mensagem antes de enviar.',
            'novaMensagem.max' => 'A mensagem pode ter no máximo 500 caracteres.',
        ]);

        $conversa = $this->quadraSelecionada !== null
            ? Conversa::firstOrCreate([
                'quadra_id' => $this->quadraSelecionada,
                'jogador_id' => Auth::id(),
            ])
            : Conversa::firstOrCreate([
                'pedido_id' => $this->pedidoSelecionado,
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
