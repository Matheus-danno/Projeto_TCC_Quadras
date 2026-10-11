<?php

namespace App\Livewire\Admin\Financeiro;

use App\Enums\ReservaStatus;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Listagem extends Component
{
    use InteractsWithComponents, WithPagination;

    public string $busca = '';

    public string $filtroQuadraId = '';

    public string $filtroDonoId = '';

    public string $filtroStatus = '';

    public string $periodo = 'mes_atual';

    public string $inicioPersonalizado = '';

    public string $fimPersonalizado = '';

    public function updating($property): void
    {
        if (in_array($property, ['busca', 'filtroQuadraId', 'filtroDonoId', 'filtroStatus', 'periodo', 'inicioPersonalizado', 'fimPersonalizado'], true)) {
            $this->resetPage();
        }
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    private function intervalo(): array
    {
        return match ($this->periodo) {
            'hoje' => [now()->startOfDay(), now()->endOfDay()],
            '7dias' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'mes_anterior' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'ano_atual' => [now()->startOfYear(), now()->endOfYear()],
            'personalizado' => $this->inicioPersonalizado !== '' && $this->fimPersonalizado !== ''
                ? [\Illuminate\Support\Carbon::parse($this->inicioPersonalizado)->startOfDay(), \Illuminate\Support\Carbon::parse($this->fimPersonalizado)->endOfDay()]
                : [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()->orderBy('nome')->get();
    }

    #[Computed]
    public function donos(): Collection
    {
        return User::query()->where('role', UserRole::DonoQuadra)->orderBy('name')->get();
    }

    protected function reservasQuery(): Builder
    {
        [$inicio, $fim] = $this->intervalo();

        return Reserva::query()
            ->with(['quadra.dono', 'user'])
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->when($this->filtroQuadraId, fn (Builder $query) => $query->where('quadra_id', $this->filtroQuadraId))
            ->when($this->filtroDonoId, fn (Builder $query) => $query->whereHas('quadra', fn ($q) => $q->where('dono_id', $this->filtroDonoId)))
            ->when($this->filtroStatus, fn (Builder $query) => $query->where('status', $this->filtroStatus))
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(function (Builder $query) use ($termo) {
                    $query->where('cliente_nome', 'like', $termo)
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $termo))
                        ->orWhereHas('quadra', fn ($q) => $q->where('nome', 'like', $termo));
                });
            })
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio');
    }

    public function valorTotalReais(Reserva $reserva): float
    {
        return $reserva->valor_total_centavos !== null
            ? $reserva->valor_total_centavos / 100.0
            : $reserva->valorEfetivoReais();
    }

    public function comissaoReais(Reserva $reserva): float
    {
        return $reserva->comissao_valor_centavos !== null ? $reserva->comissao_valor_centavos / 100.0 : 0.0;
    }

    public function valorProprietarioReais(Reserva $reserva): float
    {
        return $reserva->valor_proprietario_centavos !== null
            ? $reserva->valor_proprietario_centavos / 100.0
            : $this->valorTotalReais($reserva);
    }

    /**
     * Rótulo e cor do status exibido na tabela: reembolso tem prioridade
     * sobre o status bruto (reserva cancelada com reembolso em crédito).
     *
     * @return array{label: string, color: string}
     */
    public function statusExibicao(Reserva $reserva): array
    {
        if ($reserva->status === ReservaStatus::Cancelada && $reserva->cancelamento_tipo === 'credito') {
            return ['label' => __('Reembolsada'), 'color' => 'red'];
        }

        return match ($reserva->status) {
            ReservaStatus::Confirmada => ['label' => __('Confirmada'), 'color' => 'green'],
            ReservaStatus::Cancelada => ['label' => __('Cancelada'), 'color' => 'zinc'],
            default => ['label' => __('Pendente'), 'color' => 'orange'],
        };
    }

    #[Computed]
    public function reservas()
    {
        return $this->reservasQuery()->paginate(10);
    }

    #[Computed]
    public function resumoPeriodo(): array
    {
        $reservas = $this->reservasQuery()->get();

        return [
            'volumeTotal' => $reservas->sum(fn (Reserva $reserva) => $this->valorTotalReais($reserva)),
            'comissaoTotal' => $reservas->sum(fn (Reserva $reserva) => $this->comissaoReais($reserva)),
            'valorProprietarios' => $reservas->sum(fn (Reserva $reserva) => $this->valorProprietarioReais($reserva)),
        ];
    }

    public function exportarCsv(): StreamedResponse
    {
        $reservas = $this->reservasQuery()->get();

        return response()->streamDownload(function () use ($reservas) {
            $saida = fopen('php://output', 'w');
            fputcsv($saida, ['Data', 'Quadra', 'Proprietário', 'Cliente', 'Valor total', 'Comissão admin', 'Valor proprietário', 'Status']);

            foreach ($reservas as $reserva) {
                $status = $this->statusExibicao($reserva);

                fputcsv($saida, [
                    $reserva->data->format('d/m/Y'),
                    $reserva->quadra->nome,
                    $reserva->quadra->dono?->name ?? '—',
                    $reserva->nome_cliente,
                    number_format($this->valorTotalReais($reserva), 2, ',', '.'),
                    number_format($this->comissaoReais($reserva), 2, ',', '.'),
                    number_format($this->valorProprietarioReais($reserva), 2, ',', '.'),
                    $status['label'],
                ]);
            }

            fclose($saida);
        }, 'financeiro-admin-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        return view('livewire.admin.financeiro.listagem', [
            'statusDisponiveis' => ReservaStatus::cases(),
        ]);
    }
}
