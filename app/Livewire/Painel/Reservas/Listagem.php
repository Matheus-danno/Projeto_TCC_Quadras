<?php

namespace App\Livewire\Painel\Reservas;

use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public string $quadraId = '';

    public string $data = '';

    public string $status = '';

    public string $aba = 'todas';

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function reservas(): Collection
    {
        return Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()))
            ->with(['quadra', 'user'])
            ->when($this->quadraId, fn ($query) => $query->where('quadra_id', $this->quadraId))
            ->when($this->data, fn ($query) => $query->whereDate('data', $this->data))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->aba === 'hoje', fn ($query) => $query->whereDate('data', now()->toDateString()))
            ->when($this->aba === 'semana', fn ($query) => $query->whereBetween('data', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]))
            ->when($this->aba === 'pendentes', fn ($query) => $query->where('status', ReservaStatus::Pendente))
            // O enum ReservaStatus não tem status "concluída": tratamos como confirmada com data já passada.
            ->when($this->aba === 'concluidas', fn ($query) => $query->where('status', ReservaStatus::Confirmada)
                ->whereDate('data', '<', now()->toDateString()))
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->get();
    }

    #[Computed]
    public function resumo(): array
    {
        $baseQuery = fn () => Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()));

        $reservasSemana = $baseQuery()
            ->whereBetween('data', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
            ->with('quadra')
            ->get();

        $faturamentoSemana = $reservasSemana
            ->where('status', ReservaStatus::Confirmada)
            ->sum(function (Reserva $reserva) {
                $horas = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;

                return $reserva->quadra->valor_hora * $horas;
            });

        return [
            'hoje' => $baseQuery()->whereDate('data', now()->toDateString())->count(),
            'semana' => $reservasSemana->count(),
            'pendentes' => $baseQuery()->where('status', ReservaStatus::Pendente)->count(),
            'faturamentoSemana' => $faturamentoSemana,
        ];
    }

    public function confirmar(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        if ($reserva->status !== ReservaStatus::Pendente) {
            return;
        }

        $reserva->update(['status' => ReservaStatus::Confirmada]);

        $this->toast('Reserva confirmada.', variant: 'success');

        unset($this->reservas);
    }

    public function cancelar(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        if ($reserva->status === ReservaStatus::Cancelada) {
            return;
        }

        $reserva->update(['status' => ReservaStatus::Cancelada]);

        $this->toast('Reserva cancelada.', variant: 'success');

        unset($this->reservas);
    }

    public function render()
    {
        return view('livewire.painel.reservas.listagem', [
            'statusDisponiveis' => ReservaStatus::cases(),
        ]);
    }
}
