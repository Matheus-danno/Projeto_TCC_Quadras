<?php

namespace App\Livewire\Perfil;

use App\Enums\ReservaStatus;
use App\Models\Reserva;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MinhasReservas extends Component
{
    public array $erros = [];


    #[Computed]
    public function futuras()
    {
        return Auth::user()->reservas()
            ->with('quadra')
            ->whereDate('data', '>=', now()->toDateString())
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->get();
    }

    #[Computed]
    public function passadas()
    {
        return Auth::user()->reservas()
            ->with('quadra')
            ->whereDate('data', '<', now()->toDateString())
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->get();
    }

    /**
     * Cancela uma reserva do usuário autenticado.
     *
     * Reservas pendentes cancelam livremente. Reservas confirmadas exigem um
     * $tipo ('credito' ou 'extorno') e só podem ser canceladas até 5h antes do
     * início; com 'credito', o valor da quadra é devolvido como saldo do usuário.
     */
    public function cancelar(int $reservaId, ?string $tipo = null): void
    {
        unset($this->erros[$reservaId]);

        $reserva = Auth::user()->reservas()->with('quadra')->findOrFail($reservaId);

        if ($reserva->status === ReservaStatus::Confirmada) {
            if (! in_array($tipo, ['credito', 'extorno'], true)) {
                $this->erros[$reservaId] = 'Selecione como deseja ser reembolsado.';

                return;
            }

            if (! $reserva->podeCancelar()) {
                $this->erros[$reservaId] = 'Cancelamentos só podem ser feitos até 5h antes do início.';

                return;
            }

            if ($tipo === 'credito') {
                $user = Auth::user();
                $user->saldo_creditos = (float) $user->saldo_creditos + (float) ($reserva->quadra?->valor_hora ?? 0);
                $user->save();

                $this->dispatch('creditos-atualizados');
            }

            $reserva->update([
                'status' => ReservaStatus::Cancelada,
                'cancelamento_tipo' => $tipo,
            ]);
        } elseif ($reserva->status === ReservaStatus::Pendente) {
            $reserva->update(['status' => ReservaStatus::Cancelada]);
        } else {
            $this->erros[$reservaId] = 'Essa reserva não pode mais ser cancelada.';

            return;
        }

        unset($this->futuras, $this->passadas);
    }

    public function render()
    {
        return view('livewire.perfil.minhas-reservas');
    }
}
