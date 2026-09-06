<?php

namespace App\Livewire\Loja;

use App\Models\Produto;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    public string $categoria = '';

    public string $busca = '';

    #[Computed]
    public function produtos(): Collection
    {
        return Produto::query()
            ->where('ativo', true)
            ->when($this->categoria, fn ($query) => $query->where('categoria', $this->categoria))
            ->when($this->busca, fn ($query) => $query->where(
                fn ($query) => $query->where('nome', 'like', "%{$this->busca}%")
                    ->orWhere('descricao', 'like', "%{$this->busca}%")
            ))
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function categorias(): Collection
    {
        return Produto::query()->where('ativo', true)->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria');
    }

    public function render()
    {
        return view('livewire.loja.listagem');
    }
}
