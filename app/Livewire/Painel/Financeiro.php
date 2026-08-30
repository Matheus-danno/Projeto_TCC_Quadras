<?php

namespace App\Livewire\Painel;

use App\Enums\ReservaStatus;
use App\Models\Reserva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Financeiro extends Component
{
    use WithPagination;

    public string $periodo = 'mes_atual';

    public string $dataInicio = '';

    public string $dataFim = '';

    public function updatedPeriodo(): void
    {
        $this->resetPage();
    }

    public function updatedDataInicio(): void
    {
        $this->resetPage();
    }

    public function updatedDataFim(): void
    {
        $this->resetPage();
    }

    public function intervaloPeriodo(): array
    {
        return match ($this->periodo) {
            'mes_passado' => [
                now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            'personalizado' => [
                $this->dataInicio ?: now()->startOfMonth()->toDateString(),
                $this->dataFim ?: now()->endOfMonth()->toDateString(),
            ],
            default => [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ],
        };
    }

    public function periodoFormatado(): string
    {
        [$inicio, $fim] = $this->intervaloPeriodo();

        return Carbon::parse($inicio)->format('d/m/Y').' a '.Carbon::parse($fim)->format('d/m/Y');
    }

    public function duracaoEmHoras(Reserva $reserva): float
    {
        return (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;
    }

    protected function reservasConfirmadasQuery(): Builder
    {
        [$inicio, $fim] = $this->intervaloPeriodo();

        return Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()))
            ->where('status', ReservaStatus::Confirmada)
            ->whereBetween('data', [$inicio, $fim]);
    }

    #[Computed]
    public function faturamento(): float
    {
        return $this->reservasConfirmadasQuery()
            ->with('quadra')
            ->get()
            ->sum(fn (Reserva $reserva) => $reserva->quadra->valor_hora * $this->duracaoEmHoras($reserva));
    }

    #[Computed]
    public function faturamentoPorQuadra(): Collection
    {
        return $this->reservasConfirmadasQuery()
            ->with('quadra')
            ->get()
            ->groupBy('quadra_id')
            ->map(function (EloquentCollection $reservas) {
                $quadra = $reservas->first()->quadra;

                return [
                    'quadra' => $quadra,
                    'reservas' => $reservas->count(),
                    'faturamento' => $reservas->sum(fn (Reserva $reserva) => $quadra->valor_hora * $this->duracaoEmHoras($reserva)),
                ];
            })
            ->sortByDesc('faturamento')
            ->values();
    }

    #[Computed]
    public function reservas(): LengthAwarePaginator
    {
        return $this->reservasConfirmadasQuery()
            ->with(['quadra', 'user'])
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.painel.financeiro');
    }
}
