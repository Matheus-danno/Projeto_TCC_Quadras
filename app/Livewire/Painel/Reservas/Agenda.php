<?php

namespace App\Livewire\Painel\Reservas;

use App\Enums\ReservaStatus;
use App\Models\Conversa;
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

    public string $buscaQuadra = '';

    public ?int $reservaSelecionadaId = null;

    public string $motivoCancelamento = '';

    public bool $notificarCliente = true;

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
            ->when($this->buscaQuadra, fn ($query) => $query->where('nome', 'like', '%'.$this->buscaQuadra.'%'))
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
     * @return list<array{inicio: string, celulas: list<array{quadraId: int, ocupado: bool, status: ?ReservaStatus, cliente: ?string, reservaId: ?int}>}>
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
                    'reservaId' => $reserva?->id,
                ];
            }

            $linhas[] = ['inicio' => $inicio, 'celulas' => $celulas];
        }

        return $linhas;
    }

    #[Computed]
    public function reservaSelecionada(): ?Reserva
    {
        if (! $this->reservaSelecionadaId) {
            return null;
        }

        return Reserva::query()->with(['quadra', 'user'])->find($this->reservaSelecionadaId);
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

    public function verDetalhes(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        $this->reservaSelecionadaId = $reservaId;

        $this->modal('detalhes-reserva')->show();
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

        $this->limparCachesDaGrade();
    }

    public function pedirCancelamento(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        $this->reservaSelecionadaId = $reservaId;
        $this->motivoCancelamento = '';
        $this->notificarCliente = true;

        $this->modal('detalhes-reserva')->close();
        $this->modal('cancelar-reserva')->show();
    }

    public function cancelar(): void
    {
        $reserva = Reserva::with(['quadra', 'user'])->findOrFail($this->reservaSelecionadaId);

        $this->authorize('update', $reserva);

        if ($reserva->status !== ReservaStatus::Cancelada) {
            // Reembolso automático em crédito, mesmo mecanismo usado quando o
            // próprio jogador cancela uma reserva confirmada (MinhasReservas::cancelar()).
            if ($reserva->status === ReservaStatus::Confirmada && $reserva->user) {
                $reserva->user->increment('saldo_creditos', (float) ($reserva->quadra?->valor_hora ?? 0));
            }

            $reserva->update([
                'status' => ReservaStatus::Cancelada,
                'cancelamento_tipo' => $reserva->status === ReservaStatus::Confirmada && $reserva->user ? 'credito' : null,
                'motivo_cancelamento' => trim($this->motivoCancelamento) ?: null,
            ]);

            if ($this->notificarCliente && $reserva->user) {
                $conversa = Conversa::firstOrCreate([
                    'quadra_id' => $reserva->quadra_id,
                    'jogador_id' => $reserva->user_id,
                ]);

                $texto = __('Sua reserva de :data às :hora foi cancelada.', [
                    'data' => $reserva->data->format('d/m/Y'),
                    'hora' => substr($reserva->hora_inicio, 0, 5),
                ]);

                if (trim($this->motivoCancelamento)) {
                    $texto .= ' '.__('Motivo: :motivo', ['motivo' => trim($this->motivoCancelamento)]);
                }

                $conversa->mensagens()->create([
                    'user_id' => auth()->id(),
                    'texto' => $texto,
                ]);
            }
        }

        $this->modal('cancelar-reserva')->close();
        $this->toast('Reserva cancelada.', variant: 'success');

        $this->reservaSelecionadaId = null;
        $this->limparCachesDaGrade();
    }

    private function limparCachesDaGrade(): void
    {
        unset($this->semanas, $this->gradeHorarios, $this->reservasDoDia, $this->reservaSelecionada);
    }

    public function render()
    {
        return view('livewire.painel.reservas.agenda');
    }
}
