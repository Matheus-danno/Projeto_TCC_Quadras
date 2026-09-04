<?php

namespace App\Livewire\Painel\Reservas;

use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Agenda extends Component
{
    /**
     * Horários de início possíveis para uma reserva (quadras abrem 07h, fecham
     * 22h), mesma janela usada em Quadras\Listagem::horariosDisponiveis().
     */
    private const HORA_ABERTURA = 7;

    private const HORA_FECHAMENTO = 22;

    public string $mesAtual;

    public string $diaSelecionado;

    public string $quadraId = '';

    public function mount(): void
    {
        $this->mesAtual = now()->startOfMonth()->toDateString();
        $this->diaSelecionado = now()->toDateString();
        $this->quadraId = (string) ($this->quadras->first()?->id ?? '');
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->orderBy('nome')
            ->get();
    }

    /**
     * @return list<list<array{data: Carbon, noMes: bool, confirmadas: int, pendentes: int}>>
     */
    #[Computed]
    public function semanas(): array
    {
        $inicioMes = Carbon::parse($this->mesAtual);
        $fimMes = $inicioMes->copy()->endOfMonth();

        $inicioGrade = $inicioMes->copy()->startOfWeek(Carbon::SUNDAY);
        $fimGrade = $fimMes->copy()->endOfWeek(Carbon::SATURDAY);

        $contagens = Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()))
            ->whereBetween('data', [$inicioGrade->toDateString(), $fimGrade->toDateString()])
            ->where('status', '!=', ReservaStatus::Cancelada)
            ->get()
            ->groupBy(fn (Reserva $reserva) => $reserva->data->toDateString());

        $dias = [];
        $cursor = $inicioGrade->copy();

        while ($cursor->lte($fimGrade)) {
            $reservasDoDia = $contagens->get($cursor->toDateString(), collect());

            $dias[] = [
                'data' => $cursor->copy(),
                'noMes' => $cursor->month === $inicioMes->month,
                'confirmadas' => $reservasDoDia->where('status', ReservaStatus::Confirmada)->count(),
                'pendentes' => $reservasDoDia->where('status', ReservaStatus::Pendente)->count(),
            ];

            $cursor->addDay();
        }

        return array_chunk($dias, 7);
    }

    #[Computed]
    public function reservasDoDia(): Collection
    {
        return Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()))
            ->whereDate('data', $this->diaSelecionado)
            ->where('status', '!=', ReservaStatus::Cancelada)
            ->with(['quadra', 'user'])
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * @return list<array{inicio: string, ocupado: bool}>
     */
    #[Computed]
    public function horariosDoDia(): array
    {
        if (! $this->quadraId) {
            return [];
        }

        $reservasDaQuadra = Reserva::query()
            ->where('quadra_id', $this->quadraId)
            ->whereDate('data', $this->diaSelecionado)
            ->where('status', '!=', ReservaStatus::Cancelada)
            ->get();

        $horarios = [];

        for ($hora = self::HORA_ABERTURA; $hora < self::HORA_FECHAMENTO; $hora++) {
            $inicio = sprintf('%02d:00', $hora);
            $fim = sprintf('%02d:00', $hora + 1);

            $ocupado = $reservasDaQuadra->contains(
                fn (Reserva $reserva) => $reserva->hora_inicio < $fim && $reserva->hora_fim > $inicio
            );

            $horarios[] = ['inicio' => $inicio, 'ocupado' => $ocupado];
        }

        return $horarios;
    }

    public function mudarMes(int $delta): void
    {
        $this->mesAtual = Carbon::parse($this->mesAtual)->addMonths($delta)->startOfMonth()->toDateString();
    }

    public function selecionarDia(string $data): void
    {
        $this->diaSelecionado = $data;
    }

    public function render()
    {
        return view('livewire.painel.reservas.agenda');
    }
}
