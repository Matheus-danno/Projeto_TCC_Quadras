<?php

namespace App\Livewire\Painel;

use App\Enums\PedidoStatus;
use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Models\Pedido;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorImpl;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Financeiro extends Component
{
    use InteractsWithComponents, WithPagination;

    /**
     * Percentual retido pelo site, tanto em reservas quanto em vendas de
     * produto (a da Loja já é calculada e congelada por pedido; a de
     * reservas não é persistida — é só simulada aqui, como o resto do
     * financeiro, já que não há gateway de pagamento real integrado).
     */
    private const COMISSAO_PERCENTUAL = 5.0;

    public string $busca = '';

    public string $filtroQuadraId = '';

    public string $filtroStatus = '';

    public string $filtroTipo = 'todos';

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

    public function updatedFiltroTipo(): void
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
     * Comissão simulada de 5% sobre o valor da reserva — não é persistida
     * (diferente da comissão da Loja, congelada por pedido), pelo mesmo
     * motivo de todo o restante do financeiro de reservas ser calculado na
     * hora: não há gateway de pagamento real integrado.
     */
    public function comissaoReserva(Reserva $reserva): float
    {
        return round($this->valorReserva($reserva) * self::COMISSAO_PERCENTUAL / 100, 2);
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

    /**
     * Rótulo e cor do status de um pedido da Loja para a tabela de
     * transações (pedidos cancelados já são excluídos antes de chegar aqui).
     *
     * @return array{label: string, color: string}
     */
    public function statusTransacaoPedido(Pedido $pedido): array
    {
        return match ($pedido->status) {
            PedidoStatus::Retirado => ['label' => __('Retirado'), 'color' => 'green'],
            default => ['label' => __('Aguardando Retirada'), 'color' => 'orange'],
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
     * Reservas confirmadas e pagas do mês atual, do dono — base reaproveitada
     * por faturamentoPorQuadra, comissaoReservasMes e a tabela de transações.
     */
    #[Computed]
    public function reservasFaturaveisMesAtual(): EloquentCollection
    {
        [$inicio, $fim] = $this->intervaloMesAtual();

        return $this->reservasDoDonoQuery()
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', '!=', StatusPagamento::Isento->value)
            ->whereBetween('data', [$inicio, $fim])
            ->with('quadra')
            ->get();
    }

    /**
     * Pedidos da Loja não cancelados do mês atual, do dono — base reaproveitada
     * por faturamentoLojaMes, comissaoLojaMes, faturamentoPorProduto e a
     * tabela de transações.
     */
    #[Computed]
    public function pedidosFaturaveisMesAtual(): EloquentCollection
    {
        return Pedido::query()
            ->where('dono_id', auth()->id())
            ->where('status', '!=', PedidoStatus::Cancelado)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->with(['itens.produto', 'user'])
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
        $linhas = $this->reservasFaturaveisMesAtual
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

    #[Computed]
    public function comissaoReservasMes(): float
    {
        return (float) $this->reservasFaturaveisMesAtual->sum(fn (Reserva $reserva) => $this->comissaoReserva($reserva));
    }

    #[Computed]
    public function faturamentoLojaMes(): float
    {
        return (float) $this->pedidosFaturaveisMesAtual->sum('total');
    }

    #[Computed]
    public function comissaoLojaMes(): float
    {
        return (float) $this->pedidosFaturaveisMesAtual->sum('comissao_valor');
    }

    /**
     * Resumo geral do mês atual, unindo reservas e vendas da Loja — usado
     * pelos cards de indicadores no topo do Financeiro.
     *
     * @return array{faturamentoReservas: float, faturamentoLoja: float, faturamentoTotal: float, comissaoTotal: float, liquidoTotal: float}
     */
    #[Computed]
    public function resumoGeral(): array
    {
        $faturamentoTotal = $this->faturamentoTotalMes + $this->faturamentoLojaMes;
        $comissaoTotal = $this->comissaoReservasMes + $this->comissaoLojaMes;

        return [
            'faturamentoReservas' => $this->faturamentoTotalMes,
            'faturamentoLoja' => $this->faturamentoLojaMes,
            'faturamentoTotal' => $faturamentoTotal,
            'comissaoTotal' => $comissaoTotal,
            'liquidoTotal' => $faturamentoTotal - $comissaoTotal,
        ];
    }

    /**
     * Faturamento do mês atual por produto vendido na Loja, mesmo padrão de
     * faturamentoPorQuadra (agrupado, ordenado, com percentual relativo).
     */
    #[Computed]
    public function faturamentoPorProduto(): Collection
    {
        $linhas = $this->pedidosFaturaveisMesAtual
            ->flatMap(fn (Pedido $pedido) => $pedido->itens)
            ->groupBy('produto_id')
            ->map(fn (Collection $itens) => [
                'produto' => $itens->first()->produto,
                'faturamento' => $itens->sum(fn ($item) => $item->quantidade * $item->preco_unitario),
            ])
            ->sortByDesc('faturamento')
            ->values();

        $maximo = (float) $linhas->max('faturamento') ?: 1.0;

        return $linhas->map(fn (array $linha) => $linha + [
            'percentual' => min(100.0, ($linha['faturamento'] / $maximo) * 100),
        ]);
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
     * Consulta base das reservas-transação do mês atual, já com busca e
     * filtros aplicados — reaproveitada pela tabela unificada e pelo CSV.
     */
    protected function transacoesReservaQuery(): Builder
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

    /**
     * Consulta base dos pedidos-transação (não cancelados) do mês atual, já
     * com a busca aplicada — pedidos não têm quadra nem os status de
     * pagamento de reserva, então filtroQuadraId/filtroStatus não se aplicam.
     */
    protected function transacoesPedidoQuery(): Builder
    {
        return Pedido::query()
            ->where('dono_id', auth()->id())
            ->where('status', '!=', PedidoStatus::Cancelado)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(function (Builder $query) use ($termo) {
                    $query->where('numero_retirada', 'like', $termo)
                        ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', $termo));
                });
            });
    }

    /**
     * @return array{chave: string, tipo: string, data: Carbon, descricao: string, cliente: string, valor: float, comissao: float, statusLabel: string, statusColor: string}
     */
    protected function normalizarReserva(Reserva $reserva): array
    {
        $status = $this->statusTransacao($reserva);

        return [
            'chave' => 'reserva-'.$reserva->id,
            'tipo' => 'reserva',
            'data' => Carbon::parse($reserva->data->toDateString().' '.($reserva->hora_inicio ?? '00:00:00')),
            'descricao' => $reserva->quadra->nome,
            'cliente' => $reserva->nome_cliente,
            'valor' => $this->valorReserva($reserva),
            'comissao' => $this->comissaoReserva($reserva),
            'statusLabel' => $status['label'],
            'statusColor' => $status['color'],
        ];
    }

    /**
     * @return array{chave: string, tipo: string, data: Carbon, descricao: string, cliente: string, valor: float, comissao: float, statusLabel: string, statusColor: string}
     */
    protected function normalizarPedido(Pedido $pedido): array
    {
        $status = $this->statusTransacaoPedido($pedido);

        return [
            'chave' => 'pedido-'.$pedido->id,
            'tipo' => 'produto',
            'data' => $pedido->created_at,
            'descricao' => $pedido->itens->map(fn ($item) => $item->quantidade.'x '.$item->produto->nome)->join(', '),
            'cliente' => $pedido->user->name,
            'valor' => (float) $pedido->total,
            'comissao' => (float) $pedido->comissao_valor,
            'statusLabel' => $status['label'],
            'statusColor' => $status['color'],
        ];
    }

    /**
     * Reservas e pedidos do mês atual, já filtrados e normalizados num
     * formato comum, ordenados do mais recente para o mais antigo.
     */
    protected function transacoesUnificadas(): Collection
    {
        $linhas = collect();

        if ($this->filtroTipo !== 'produtos') {
            $linhas = $linhas->concat(
                $this->transacoesReservaQuery()->with(['quadra', 'user'])->get()
                    ->map(fn (Reserva $reserva) => $this->normalizarReserva($reserva))
            );
        }

        if ($this->filtroTipo !== 'reservas') {
            $linhas = $linhas->concat(
                $this->transacoesPedidoQuery()->with(['itens.produto', 'user'])->get()
                    ->map(fn (Pedido $pedido) => $this->normalizarPedido($pedido))
            );
        }

        return $linhas->sortByDesc('data')->values();
    }

    #[Computed]
    public function transacoes(): LengthAwarePaginator
    {
        $todas = $this->transacoesUnificadas();
        $porPagina = 5;
        $pagina = Paginator::resolveCurrentPage('page');

        return new LengthAwarePaginatorImpl(
            $todas->forPage($pagina, $porPagina)->values(),
            $todas->count(),
            $porPagina,
            $pagina,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * Faturamento total (reservas confirmadas e pagas + vendas da Loja) dos
     * últimos 6 meses, incluindo o atual, com o percentual relativo ao mês
     * de maior faturamento — usado no gráfico de tendência.
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

        $pedidos = Pedido::query()
            ->where('dono_id', auth()->id())
            ->where('status', '!=', PedidoStatus::Cancelado)
            ->where('created_at', '>=', $inicioIntervalo)
            ->get()
            ->groupBy(fn (Pedido $pedido) => $pedido->created_at->format('Y-m'));

        $meses = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->startOfMonth()->subMonths($i);
            $chave = $mes->format('Y-m');

            $faturamentoReservas = (float) $reservas->get($chave, collect())
                ->sum(fn (Reserva $reserva) => $this->valorReserva($reserva));

            $faturamentoLoja = (float) $pedidos->get($chave, collect())->sum('total');

            $meses[] = [
                'mes' => $mes,
                'label' => $mes->translatedFormat('M'),
                'faturamento' => $faturamentoReservas + $faturamentoLoja,
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
     * Exporta as transações (reservas + pedidos) do mês atual, com a busca e
     * os filtros ativos, como CSV para download.
     */
    public function exportarCsv(): StreamedResponse
    {
        $transacoes = $this->transacoesUnificadas();

        $nomeArquivo = 'transacoes-'.now()->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($transacoes) {
            $saida = fopen('php://output', 'w');
            fputcsv($saida, ['Tipo', 'Data', 'Descrição', 'Cliente', 'Valor', 'Comissão', 'Status']);

            foreach ($transacoes as $linha) {
                fputcsv($saida, [
                    $linha['tipo'] === 'reserva' ? 'Quadra' : 'Produto',
                    $linha['data']->format('d/m/Y'),
                    $linha['descricao'],
                    $linha['cliente'],
                    number_format($linha['valor'], 2, ',', '.'),
                    number_format($linha['comissao'], 2, ',', '.'),
                    $linha['statusLabel'],
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
