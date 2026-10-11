<?php

namespace App\Livewire\Admin\Reservas;

use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Listagem extends Component
{
    use InteractsWithComponents, WithPagination;

    public string $busca = '';

    public string $filtroQuadraId = '';

    public string $filtroStatus = '';

    public string $aba = 'todas';

    public function updating($property): void
    {
        if (in_array($property, ['busca', 'filtroQuadraId', 'filtroStatus', 'aba'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function reservas()
    {
        return Reserva::query()
            ->with(['quadra.dono', 'user'])
            ->when($this->filtroQuadraId, fn (Builder $query) => $query->where('quadra_id', $this->filtroQuadraId))
            ->when($this->filtroStatus, fn (Builder $query) => $query->where('status', $this->filtroStatus))
            ->when($this->aba === 'canceladas', fn (Builder $query) => $query->where('status', ReservaStatus::Cancelada))
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(fn ($q) => $q->where('cliente_nome', 'like', $termo)
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $termo))
                    ->orWhereHas('quadra', fn ($q) => $q->where('nome', 'like', $termo)));
            })
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->paginate(10);
    }

    /**
     * Impacto da reserva na comissão: quanto já foi contabilizado (positivo)
     * ou estornado (quando cancelada após ter sido paga).
     *
     * @return array{valor: float, estornado: bool}
     */
    public function impactoComissao(Reserva $reserva): array
    {
        $valor = $reserva->comissao_valor_centavos !== null ? $reserva->comissao_valor_centavos / 100.0 : 0.0;

        return [
            'valor' => $valor,
            'estornado' => $reserva->status === ReservaStatus::Cancelada && $valor > 0,
        ];
    }

    public function render()
    {
        return view('livewire.admin.reservas.listagem', [
            'statusDisponiveis' => ReservaStatus::cases(),
        ]);
    }
}
