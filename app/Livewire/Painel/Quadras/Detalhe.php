<?php

namespace App\Livewire\Painel\Quadras;

use App\Enums\ReservaStatus;
use App\Models\AvaliacaoQuadra;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Detalhe extends Component
{
    use InteractsWithComponents;

    /**
     * Janela usada para calcular a taxa de ocupação: horas de funcionamento
     * por dia (07h-22h), mesma janela usada em Quadras\Listagem::horariosDisponiveis().
     */
    private const HORAS_FUNCIONAMENTO_POR_DIA = 15;

    private const DIAS_TAXA_OCUPACAO = 30;

    public Quadra $quadra;

    public function mount(Quadra $quadra): void
    {
        $this->authorize('view', $quadra);

        $this->quadra = $quadra;
    }

    #[Computed]
    public function indicadores(): array
    {
        $reservas = Reserva::query()->where('quadra_id', $this->quadra->id);

        $confirmadas = (clone $reservas)->where('status', ReservaStatus::Confirmada)->get();

        $horasReserva = fn (Reserva $reserva) => (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;

        $faturamentoGerado = $confirmadas->sum(
            fn (Reserva $reserva) => $this->quadra->valor_hora * $horasReserva($reserva)
        );

        $inicioPeriodo = now()->subDays(self::DIAS_TAXA_OCUPACAO)->startOfDay();

        $horasReservadasNoPeriodo = $confirmadas
            ->filter(fn (Reserva $reserva) => $reserva->data->greaterThanOrEqualTo($inicioPeriodo))
            ->sum($horasReserva);

        $horasDisponiveisNoPeriodo = self::DIAS_TAXA_OCUPACAO * self::HORAS_FUNCIONAMENTO_POR_DIA;

        return [
            'totalReservas' => (clone $reservas)->count(),
            'faturamentoGerado' => $faturamentoGerado,
            'avaliacaoMedia' => $this->quadra->notaMedia() ?? 5.0,
            'taxaOcupacao' => (int) min(100, round(($horasReservadasNoPeriodo / $horasDisponiveisNoPeriodo) * 100)),
        ];
    }

    #[Computed]
    public function avaliacoesRecentes(): Collection
    {
        return AvaliacaoQuadra::query()
            ->where('quadra_id', $this->quadra->id)
            ->with('autor')
            ->latest()
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function proximasReservas(): Collection
    {
        return Reserva::query()
            ->where('quadra_id', $this->quadra->id)
            ->where('status', '!=', ReservaStatus::Cancelada)
            ->whereDate('data', '>=', now()->toDateString())
            ->with('user')
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->limit(10)
            ->get();
    }

    public function cancelar(): void
    {
        $this->authorize('update', $this->quadra);

        $this->quadra->update(['ativa' => false]);

        $this->toast(__('Quadra desativada.'), variant: 'success');
    }

    public function ativar(): void
    {
        $this->authorize('update', $this->quadra);

        $this->quadra->update(['ativa' => true]);

        $this->toast(__('Quadra ativada novamente.'), variant: 'success');
    }

    public function render()
    {
        return view('livewire.painel.quadras.detalhe');
    }
}
