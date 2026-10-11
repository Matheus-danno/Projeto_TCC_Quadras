<?php

namespace App\Livewire\Admin\Quadras;

use App\Enums\ReservaStatus;
use App\Enums\StatusAprovacaoQuadra;
use App\Enums\StatusPagamento;
use App\Models\Quadra;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Listagem extends Component
{
    use InteractsWithComponents, WithPagination;

    public string $busca = '';

    public string $filtroStatus = '';

    public function updating($property): void
    {
        if (in_array($property, ['busca', 'filtroStatus'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function quadras()
    {
        return Quadra::query()
            ->with('dono')
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(fn ($q) => $q->where('nome', 'like', $termo)
                    ->orWhereHas('dono', fn ($q) => $q->where('name', 'like', $termo)));
            })
            ->when($this->filtroStatus, fn (Builder $query) => $query->where('status_aprovacao', $this->filtroStatus))
            ->orderBy('nome')
            ->paginate(10);
    }

    /**
     * Receita bruta (valor total das reservas confirmadas e pagas) gerada por esta quadra.
     */
    public function receitaGerada(Quadra $quadra): float
    {
        return (int) $quadra->reservas()
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', StatusPagamento::Pago)
            ->sum('valor_total_centavos') / 100.0;
    }

    public function aprovar(int $quadraId): void
    {
        Quadra::findOrFail($quadraId)->update(['status_aprovacao' => StatusAprovacaoQuadra::Aprovada]);

        $this->toast(__('Quadra aprovada.'), variant: 'success');

        unset($this->quadras);
    }

    public function suspender(int $quadraId): void
    {
        Quadra::findOrFail($quadraId)->update(['status_aprovacao' => StatusAprovacaoQuadra::Suspensa]);

        $this->toast(__('Quadra suspensa.'), variant: 'success');

        unset($this->quadras);
    }

    public function render()
    {
        return view('livewire.admin.quadras.listagem', [
            'statusDisponiveis' => StatusAprovacaoQuadra::cases(),
        ]);
    }
}
