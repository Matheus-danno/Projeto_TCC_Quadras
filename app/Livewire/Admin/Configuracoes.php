<?php

namespace App\Livewire\Admin;

use App\Models\ComissaoConfiguracao;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Configuracoes extends Component
{
    use InteractsWithComponents;

    public string $percentual = '';

    public function mount(): void
    {
        $this->percentual = number_format(ComissaoConfiguracao::percentualAtual(), 2, '.', '');
    }

    #[Computed]
    public function historico(): Collection
    {
        return ComissaoConfiguracao::query()->with('alteradoPor')->latest('id')->limit(20)->get();
    }

    public function salvar(): void
    {
        $validated = $this->validate([
            'percentual' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        ComissaoConfiguracao::definirPercentual((float) $validated['percentual'], auth()->user());

        $this->toast(__('Nova porcentagem de comissão salva. Vale só para reservas futuras.'), variant: 'success');

        unset($this->historico);
    }

    public function render()
    {
        return view('livewire.admin.configuracoes');
    }
}
