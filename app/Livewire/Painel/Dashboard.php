<?php

namespace App\Livewire\Painel;

use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    use InteractsWithComponents;

    #[Computed]
    public function indicadores(): array
    {
        $reservasDoMesQuery = Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()))
            ->whereYear('data', now()->year)
            ->whereMonth('data', now()->month);

        $faturamentoDoMes = (clone $reservasDoMesQuery)
            ->where('status', ReservaStatus::Confirmada)
            ->with('quadra')
            ->get()
            ->sum(function (Reserva $reserva) {
                $horas = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;

                return $reserva->quadra->valor_hora * $horas;
            });

        return [
            'quantidadeQuadras' => Quadra::query()->where('dono_id', auth()->id())->count(),
            'reservasDoMes' => (clone $reservasDoMesQuery)->count(),
            'faturamentoDoMes' => $faturamentoDoMes,
            // Depende de um sistema de avaliações que ainda não existe no produto.
            'avaliacaoMedia' => '—',
        ];
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->withCount('reservas')
            ->orderBy('nome')
            ->limit(5)
            ->get();
    }

    public function cancelar(int $quadraId): void
    {
        $quadra = Quadra::findOrFail($quadraId);

        $this->authorize('update', $quadra);

        $quadra->update(['ativa' => false]);

        $this->toast('Quadra cancelada.', variant: 'success');

        unset($this->quadras);
    }

    public function render()
    {
        return view('livewire.painel.dashboard');
    }
}
