<?php

namespace App\Livewire\Admin\Reservas;

use App\Enums\ReservaStatus;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public string $quadraId = '';

    public string $donoId = '';

    public string $status = '';

    public string $dataInicio = '';

    public string $dataFim = '';

    #[Computed]
    public function reservas(): Collection
    {
        return Reserva::query()
            ->with(['quadra.dono', 'user'])
            ->when($this->quadraId, fn ($query) => $query->where('quadra_id', $this->quadraId))
            ->when($this->donoId, fn ($query) => $query->whereHas(
                'quadra',
                fn ($quadraQuery) => $quadraQuery->where('dono_id', $this->donoId)
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->dataInicio, fn ($query) => $query->whereDate('data', '>=', $this->dataInicio))
            ->when($this->dataFim, fn ($query) => $query->whereDate('data', '<=', $this->dataFim))
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->get();
    }

    #[Computed]
    public function resumo(): array
    {
        $reservas = $this->reservas;

        return [
            'quantidade' => $reservas->count(),
            'valorTotalConfirmadas' => $reservas
                ->where('status', ReservaStatus::Confirmada)
                ->sum(fn (Reserva $reserva) => (float) $reserva->quadra->valor_hora),
        ];
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function donos(): Collection
    {
        return User::query()
            ->where('role', UserRole::DonoQuadra->value)
            ->orderBy('name')
            ->get();
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

        unset($this->reservas, $this->resumo);
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

        unset($this->reservas, $this->resumo);
    }

    public function render()
    {
        return view('livewire.admin.reservas.listagem', [
            'statusDisponiveis' => ReservaStatus::cases(),
        ]);
    }
}
