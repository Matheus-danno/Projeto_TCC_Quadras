<?php

namespace App\Livewire\Loja;

use App\Models\Produto;
use App\Support\Carrinho;
use Livewire\Component;

class Detalhe extends Component
{
    public Produto $produto;

    public int $quantidade = 1;

    public bool $precisaLogin = false;

    public ?string $mensagemSucesso = null;

    public function mount(Produto $produto): void
    {
        $this->produto = $produto;
    }

    public function adicionarAoCarrinho(): void
    {
        if (! auth()->check()) {
            $this->precisaLogin = true;

            return;
        }

        $this->precisaLogin = false;
        $this->mensagemSucesso = null;

        $quantidade = max(1, $this->quantidade);

        Carrinho::adicionar($this->produto->id, $quantidade);

        $this->mensagemSucesso = "{$this->produto->nome} adicionado ao carrinho.";
    }

    public function render()
    {
        return view('livewire.loja.detalhe');
    }
}
