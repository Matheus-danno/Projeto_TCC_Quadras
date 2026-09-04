<?php

namespace App\Livewire\Painel\Reservas;

use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\Reserva;
use Carbon\Carbon;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Agenda extends Component
{
    use InteractsWithComponents;

    /**
     * Horários de início possíveis para uma reserva (quadras abrem 07h, fecham
     * 22h), mesma janela usada em Quadras\Listagem::horariosDisponiveis().
     */
    private const HORA_ABERTURA = 7;

    private const HORA_FECHAMENTO = 22;

    public string $mesAtual;

    public string $diaSelecionado;

    public ?int $slotQuadraId = null;

    public ?string $slotHorario = null;

    public function mount(): void
    {
        $this->mesAtual = now()->startOfMonth()->toDateString();
        $this->diaSelecionado = now()->toDateString();
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
     * Grade do dia selecionado: uma linha por horário, uma coluna por quadra
     * (mesma janela de horários usada em Quadras\Listagem::horariosDisponiveis()),
     * para o dono comparar a ocupação de todas as quadras de uma vez.
     *
     * @return list<array{inicio: string, celulas: list<array{quadraId: int, ocupado: bool, status: ?ReservaStatus, cliente: ?string}>}>
     */
    #[Computed]
    public function gradeHorarios(): array
    {
        if ($this->quadras->isEmpty()) {
            return [];
        }

        $reservasDoDiaPorQuadra = Reserva::query()
            ->whereIn('quadra_id', $this->quadras->pluck('id'))
            ->whereDate('data', $this->diaSelecionado)
            ->where('status', '!=', ReservaStatus::Cancelada)
            ->get()
            ->groupBy('quadra_id');

        $linhas = [];

        for ($hora = self::HORA_ABERTURA; $hora < self::HORA_FECHAMENTO; $hora++) {
            $inicio = sprintf('%02d:00', $hora);
            $fim = sprintf('%02d:00', $hora + 1);

            $celulas = [];

            foreach ($this->quadras as $quadra) {
                $reserva = $reservasDoDiaPorQuadra
                    ->get($quadra->id, collect())
                    ->first(fn (Reserva $r) => $r->hora_inicio < $fim && $r->hora_fim > $inicio);

                $celulas[] = [
                    'quadraId' => $quadra->id,
                    'ocupado' => (bool) $reserva,
                    'status' => $reserva?->status,
                    'cliente' => $reserva?->nome_cliente,
                ];
            }

            $linhas[] = ['inicio' => $inicio, 'celulas' => $celulas];
        }

        return $linhas;
    }

    public function mudarMes(int $delta): void
    {
        $this->mesAtual = Carbon::parse($this->mesAtual)->addMonths($delta)->startOfMonth()->toDateString();
    }

    public function selecionarDia(string $data): void
    {
        $this->diaSelecionado = $data;
    }

    public function abrirAgendamento(int $quadraId, string $horario): void
    {
        $this->slotQuadraId = $quadraId;
        $this->slotHorario = $horario;

        $this->modal('agendar-horario')->show();
    }

    public function render()
    {
        return view('livewire.painel.reservas.agenda');
    }
}
