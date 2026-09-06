<?php

namespace App\Livewire\Painel;

use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Financeiro extends Component
{
    use InteractsWithComponents, WithPagination;

    public string $busca = '';

    public string $filtroQuadraId = '';

    public string $filtroStatus = '';

    public bool $editandoChavePix = false;

    public string $chavePixRecebimento = '';

    public function mount(): void
    {
        $this->chavePixRecebimento = auth()->user()->chave_pix_recebimento ?? '';
    }

    public function updatedBusca(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroQuadraId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroStatus(): void
    {
        $this->resetPage();
    }

    private function intervaloMesAtual(): array
    {
        return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
    }

    public function duracaoEmHoras(Reserva $reserva): float
    {
        return (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;
    }

    /**
     * Valor da reserva: usa o valor lançado manualmente (Agendamento Manual)
     * quando existir, senão calcula a partir do valor/hora da quadra.
     */
    public function valorReserva(Reserva $reserva): float
    {
        if ($reserva->valor !== null) {
            return (float) $reserva->valor;
        }

        return (float) $reserva->quadra->valor_hora * $this->duracaoEmHoras($reserva);
    }

    /**
     * Rótulo e cor do status de uma transação: reembolso tem prioridade
     * (reserva cancelada com reembolso em crédito), depois o status de
     * pagamento lançado na reserva.
     *
     * @return array{label: string, color: string}
     */
    public function statusTransacao(Reserva $reserva): array
    {
        if ($reserva->status === ReservaStatus::Cancelada && $reserva->cancelamento_tipo === 'credito') {
            return ['label' => __('Reembolsado'), 'color' => 'red'];
        }

        return match ($reserva->status_pagamento) {
            StatusPagamento::Pago => ['label' => __('Pago'), 'color' => 'green'],
            StatusPagamento::Isento => ['label' => __('Isento'), 'color' => 'zinc'],
            default => ['label' => __('Pendente'), 'color' => 'orange'],
        };
    }

    protected function reservasDoDonoQuery(): Builder
    {
        return Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()));
    }

    #[Computed]
    public function quadras(): EloquentCollection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->orderBy('nome')
            ->get();
    }

    /**
     * Faturamento confirmado do mês atual, agrupado por quadra e ordenado do
     * maior para o menor, com o percentual relativo à quadra de maior
     * faturamento (para a barra no card "Faturamento por quadra").
     */
    #[Computed]
    public function faturamentoPorQuadra(): Collection
    {
        [$inicio, $fim] = $this->intervaloMesAtual();

        $linhas = $this->reservasDoDonoQuery()
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', '!=', StatusPagamento::Isento->value)
            ->whereBetween('data', [$inicio, $fim])
            ->with('quadra')
            ->get()
            ->groupBy('quadra_id')
            ->map(fn (EloquentCollection $reservas) => [
                'quadra' => $reservas->first()->quadra,
                'faturamento' => $reservas->sum(fn (Reserva $reserva) => $this->valorReserva($reserva)),
            ])
            ->sortByDesc('faturamento')
            ->values();

        $maximo = (float) $linhas->max('faturamento') ?: 1.0;

        return $linhas->map(fn (array $linha) => $linha + [
            'percentual' => min(100.0, ($linha['faturamento'] / $maximo) * 100),
        ]);
    }

    #[Computed]
    public function faturamentoTotalMes(): float
    {
        return $this->faturamentoPorQuadra->sum('faturamento');
    }

    /**
     * Próxima reserva confirmada (e paga) a acontecer, usada como estimativa
     * simulada de repasse — não há gateway de pagamento real integrado.
     *
     * @return array{dias: int, valor: float}|null
     */
    #[Computed]
    public function proximaLiberacao(): ?array
    {
        $reserva = $this->reservasDoDonoQuery()
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', '!=', StatusPagamento::Isento->value)
            ->whereDate('data', '>=', now()->toDateString())
            ->with('quadra')
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->first();

        if (! $reserva) {
            return null;
        }

        return [
            'dias' => (int) now()->startOfDay()->diffInDays($reserva->data),
            'valor' => $this->valorReserva($reserva),
        ];
    }

    /**
     * Consulta base das transações do mês atual, já com busca e filtros
     * aplicados — reaproveitada pela tabela paginada e pela exportação CSV.
     */
    protected function transacoesQuery(): Builder
    {
        [$inicio, $fim] = $this->intervaloMesAtual();

        return $this->reservasDoDonoQuery()
            ->where(function (Builder $query) {
                $query->where('status', ReservaStatus::Confirmada)
                    ->orWhere(function (Builder $query) {
                        $query->where('status', ReservaStatus::Cancelada)->where('cancelamento_tipo', 'credito');
                    });
            })
            ->whereBetween('data', [$inicio, $fim])
            ->when($this->filtroQuadraId, fn (Builder $query) => $query->where('quadra_id', $this->filtroQuadraId))
            ->when($this->filtroStatus === 'reembolsado', fn (Builder $query) => $query->where('status', ReservaStatus::Cancelada))
            ->when(in_array($this->filtroStatus, ['pago', 'pendente', 'isento'], true), fn (Builder $query) => $query
                ->where('status', ReservaStatus::Confirmada)
                ->where('status_pagamento', $this->filtroStatus))
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(function (Builder $query) use ($termo) {
                    $query->where('cliente_nome', 'like', $termo)
                        ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', $termo))
                        ->orWhereHas('quadra', fn (Builder $query) => $query->where('nome', 'like', $termo));
                });
            });
    }

    #[Computed]
    public function transacoes(): LengthAwarePaginator
    {
        return $this->transacoesQuery()
            ->with(['quadra', 'user'])
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->paginate(5);
    }

    /**
     * Faturamento confirmado (não isento) dos últimos 6 meses, incluindo o
     * atual, com o percentual relativo ao mês de maior faturamento — usado
     * no gráfico de tendência.
     *
     * @return list<array{mes: Carbon, label: string, faturamento: float, percentual: float, atual: bool}>
     */
    #[Computed]
    public function faturamentoUltimosMeses(): array
    {
        $inicioIntervalo = now()->startOfMonth()->subMonths(5);

        $reservas = $this->reservasDoDonoQuery()
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', '!=', StatusPagamento::Isento->value)
            ->where('data', '>=', $inicioIntervalo->toDateString())
            ->with('quadra')
            ->get()
            ->groupBy(fn (Reserva $reserva) => $reserva->data->format('Y-m'));

        $meses = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->startOfMonth()->subMonths($i);

            $meses[] = [
                'mes' => $mes,
                'label' => $mes->translatedFormat('M'),
                'faturamento' => (float) $reservas->get($mes->format('Y-m'), collect())
                    ->sum(fn (Reserva $reserva) => $this->valorReserva($reserva)),
                'atual' => $i === 0,
            ];
        }

        $maximo = (float) collect($meses)->max('faturamento') ?: 1.0;

        return collect($meses)
            ->map(fn (array $linha) => $linha + [
                'percentual' => min(100.0, ($linha['faturamento'] / $maximo) * 100),
            ])
            ->all();
    }

    /**
     * Exporta as transações do mês atual (com a busca e os filtros ativos)
     * como CSV para download.
     */
    public function exportarCsv(): StreamedResponse
    {
        $transacoes = $this->transacoesQuery()
            ->with(['quadra', 'user'])
            ->orderByDesc('data')
            ->orderByDesc('hora_inicio')
            ->get();

        $nomeArquivo = 'transacoes-'.now()->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($transacoes) {
            $saida = fopen('php://output', 'w');
            fputcsv($saida, ['Data', 'Quadra', 'Cliente', 'Valor', 'Status']);

            foreach ($transacoes as $transacao) {
                $status = $this->statusTransacao($transacao);

                fputcsv($saida, [
                    $transacao->data->format('d/m/Y'),
                    $transacao->quadra->nome,
                    $transacao->nome_cliente,
                    number_format($this->valorReserva($transacao), 2, ',', '.'),
                    $status['label'],
                ]);
            }

            fclose($saida);
        }, $nomeArquivo, ['Content-Type' => 'text/csv']);
    }

    public function editarChavePix(): void
    {
        $this->editandoChavePix = true;
    }

    public function cancelarEdicaoChavePix(): void
    {
        $this->editandoChavePix = false;
        $this->chavePixRecebimento = auth()->user()->chave_pix_recebimento ?? '';
        $this->resetErrorBag('chavePixRecebimento');
    }

    public function salvarChavePix(): void
    {
        $this->validate(['chavePixRecebimento' => ['nullable', 'string', 'max:255']]);

        auth()->user()->update(['chave_pix_recebimento' => $this->chavePixRecebimento ?: null]);

        $this->editandoChavePix = false;
        $this->toast('Chave Pix atualizada.', variant: 'success');
    }

    /**
     * Mascara a chave Pix para exibição (ex.: "ana.silva@email.com" vira
     * "••••.silva@email.com").
     */
    public function chavePixMascarada(): ?string
    {
        $chave = auth()->user()->chave_pix_recebimento;

        if (! $chave) {
            return null;
        }

        if (str_contains($chave, '@')) {
            [$local, $dominio] = explode('@', $chave, 2);

            if (str_contains($local, '.')) {
                $partes = explode('.', $local);
                $ultima = array_pop($partes);

                return '••••.'.$ultima.'@'.$dominio;
            }

            return '••••@'.$dominio;
        }

        return '••••'.substr($chave, -4);
    }

    public function render()
    {
        return view('livewire.painel.financeiro');
    }
}
