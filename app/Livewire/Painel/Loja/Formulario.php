<?php

namespace App\Livewire\Painel\Loja;

use App\Models\Produto;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Formulario extends Component
{
    use InteractsWithComponents;
    use WithFileUploads;

    public ?Produto $produto = null;

    public string $nome = '';

    public string $descricao = '';

    public string $categoria = '';

    public string $preco = '';

    public string $estoque = '';

    public bool $ativo = true;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novaImagem = null;

    public ?string $bloqueioExclusao = null;

    public function mount(?Produto $produto = null): void
    {
        if ($produto?->exists) {
            $this->authorize('update', $produto);

            $this->produto = $produto;
            $this->nome = $produto->nome;
            $this->descricao = $produto->descricao ?? '';
            $this->categoria = $produto->categoria ?? '';
            $this->preco = (string) $produto->preco;
            $this->estoque = (string) $produto->estoque;
            $this->ativo = $produto->ativo;
        }
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'max:1000'],
            'categoria' => ['required', 'string', 'max:255'],
            'preco' => ['required', 'numeric', 'min:0.01'],
            'estoque' => ['required', 'integer', 'min:0'],
            'ativo' => ['boolean'],
            'novaImagem' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function removerImagemAtual(): void
    {
        if (! $this->produto?->imagem) {
            return;
        }

        $this->authorize('update', $this->produto);

        Storage::disk('public')->delete($this->produto->imagem);
        $this->produto->update(['imagem' => null]);
    }

    public function salvar(): void
    {
        $validated = $this->validate();

        $dados = [
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'],
            'categoria' => $validated['categoria'],
            'preco' => $validated['preco'],
            'estoque' => $validated['estoque'],
            'ativo' => $this->ativo,
        ];

        if ($this->produto) {
            $this->authorize('update', $this->produto);

            $this->produto->update($dados);
        } else {
            $this->produto = Produto::create([
                ...$dados,
                'dono_id' => auth()->id(),
            ]);
        }

        if ($this->novaImagem) {
            if ($this->produto->imagem) {
                Storage::disk('public')->delete($this->produto->imagem);
            }

            $caminho = $this->novaImagem->store('produtos/'.$this->produto->id, 'public');

            $this->produto->update(['imagem' => $caminho]);
        }

        $this->redirect(route('painel.loja.show', $this->produto), navigate: true);
    }

    public function pedirExclusao(): void
    {
        $this->authorize('delete', $this->produto);

        $this->bloqueioExclusao = $this->produto->itensPedido()->exists()
            ? __('Este produto já foi vendido e não pode ser excluído. Desative-o em vez de excluir para preservar o histórico de pedidos.')
            : null;

        $this->modal('excluir-produto')->show();
    }

    public function excluir(): void
    {
        $this->authorize('delete', $this->produto);

        if ($this->produto->itensPedido()->exists()) {
            $this->bloqueioExclusao = __('Este produto já foi vendido e não pode ser excluído. Desative-o em vez de excluir para preservar o histórico de pedidos.');

            return;
        }

        if ($this->produto->imagem) {
            Storage::disk('public')->delete($this->produto->imagem);
        }

        $this->produto->delete();

        $this->modal('excluir-produto')->close();
        $this->toast(__('Produto excluído com sucesso.'), variant: 'success');

        $this->redirect(route('painel.loja'), navigate: true);
    }

    public function render()
    {
        return view('livewire.painel.loja.formulario');
    }
}
