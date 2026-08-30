<?php

namespace App\Livewire\Quadras;

use App\Enums\ReservaStatus;
use App\Models\Reserva;
use Livewire\Component;

class Pagamento extends Component
{
    public Reserva $reserva;

    public string $metodo = 'pix';

    public function mount(Reserva $reserva): void
    {
        abort_unless(auth()->id() === $reserva->user_id, 403);
        abort_if($reserva->status === ReservaStatus::Cancelada, 404);

        $this->reserva = $reserva;
    }

    /**
     * Código Pix "copia e cola" (simulado, sem gateway de pagamento real).
     */
    public function codigoPix(): string
    {
        $valorCentavos = (int) round(((float) $this->reserva->quadra->valor_hora) * 100);
        $identificador = str_pad((string) $this->reserva->id, 8, '0', STR_PAD_LEFT);
        $hash = strtoupper(substr(md5($this->reserva->id.$valorCentavos), 0, 4));

        return "00020126580014BR.GOV.BCB.PIX0136{$identificador}alugaquadra5204000053039865802BR5913AlugaQuadra6009SAO PAULO62070503***6304{$hash}";
    }

    public function confirmarPagamento(): void
    {
        if ($this->reserva->status === ReservaStatus::Confirmada) {
            $this->redirect(route('reservas.confirmacao', $this->reserva), navigate: false);

            return;
        }

        $this->reserva->update([
            'status' => ReservaStatus::Confirmada,
            'metodo_pagamento' => $this->metodo,
        ]);

        $this->redirect(route('reservas.confirmacao', $this->reserva), navigate: false);
    }

    /**
     * O crédito só pode ser usado quando cobre o valor total da reserva.
     */
    public function podePagarComCredito(): bool
    {
        return (float) auth()->user()->saldo_creditos >= (float) $this->reserva->quadra->valor_hora;
    }

    public function pagarComCredito(): void
    {
        abort_unless($this->podePagarComCredito(), 403);

        if ($this->reserva->status === ReservaStatus::Confirmada) {
            $this->redirect(route('reservas.confirmacao', $this->reserva), navigate: false);

            return;
        }

        auth()->user()->decrement('saldo_creditos', (float) $this->reserva->quadra->valor_hora);

        $this->reserva->update([
            'status' => ReservaStatus::Confirmada,
            'metodo_pagamento' => 'credito',
        ]);

        $this->redirect(route('reservas.confirmacao', $this->reserva), navigate: false);
    }

    public function render()
    {
        return view('livewire.quadras.pagamento');
    }
}
