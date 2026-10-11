<?php

namespace App\Livewire\Admin;

use App\Enums\ReservaStatus;
use App\Enums\StatusCadastro;
use App\Enums\StatusPagamento;
use App\Enums\UserRole;
use App\Models\CadastroHistorico;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Carbon\CarbonInterface;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    use InteractsWithComponents;

    public string $periodo = 'mes_atual';

    public string $inicioPersonalizado = '';

    public string $fimPersonalizado = '';

    public function updatedPeriodo(): void
    {
        $this->limparComputeds();
    }

    public function updatedInicioPersonalizado(): void
    {
        $this->limparComputeds();
    }

    public function updatedFimPersonalizado(): void
    {
        $this->limparComputeds();
    }

    private function limparComputeds(): void
    {
        unset($this->indicadores, $this->graficoComissao, $this->rankingQuadras, $this->rankingHorarios, $this->rankingDiasSemana);
    }

    /**
     * Intervalo [início, fim] do período selecionado.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function intervalo(): array
    {
        return match ($this->periodo) {
            'hoje' => [now()->startOfDay(), now()->endOfDay()],
            '7dias' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'mes_anterior' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'ano_atual' => [now()->startOfYear(), now()->endOfYear()],
            'personalizado' => $this->intervaloPersonalizadoOuPadrao(),
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function intervaloPersonalizadoOuPadrao(): array
    {
        if ($this->inicioPersonalizado === '' || $this->fimPersonalizado === '') {
            return [now()->startOfMonth(), now()->endOfMonth()];
        }

        return [
            Carbon::parse($this->inicioPersonalizado)->startOfDay(),
            Carbon::parse($this->fimPersonalizado)->endOfDay(),
        ];
    }

    /**
     * Período imediatamente anterior, com a mesma duração do selecionado —
     * usado para calcular a variação percentual exibida nos cards de KPI.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function intervaloAnterior(): array
    {
        [$inicio, $fim] = $this->intervalo();

        $dias = $inicio->diffInDays($fim) + 1;

        return [
            $inicio->clone()->subDays($dias),
            $inicio->clone()->subDay()->endOfDay(),
        ];
    }

    /**
     * Reservas que contam para faturamento/comissão (confirmadas e pagas)
     * dentro de um intervalo de datas.
     */
    private function reservasFaturaveisEntre(CarbonInterface $inicio, CarbonInterface $fim): Builder
    {
        return Reserva::query()
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', StatusPagamento::Pago)
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()]);
    }

    /**
     * @return array{comissaoTotal: float, volumeTotal: float, numeroReservas: int, ticketMedio: float, quadrasAtivas: int, usuariosCadastrados: int, variacao: array<string, float|null>}
     */
    #[Computed]
    public function indicadores(): array
    {
        [$inicio, $fim] = $this->intervalo();
        [$inicioAnterior, $fimAnterior] = $this->intervaloAnterior();

        $atual = $this->resumoPeriodo($inicio, $fim);
        $anterior = $this->resumoPeriodo($inicioAnterior, $fimAnterior);

        return [
            ...$atual,
            'quadrasAtivas' => Quadra::query()->where('ativa', true)->count(),
            'usuariosCadastrados' => User::query()->where('role', '!=', UserRole::Admin)->count(),
            'variacao' => [
                'comissaoTotal' => $this->variacaoPercentual($atual['comissaoTotal'], $anterior['comissaoTotal']),
                'volumeTotal' => $this->variacaoPercentual($atual['volumeTotal'], $anterior['volumeTotal']),
                'numeroReservas' => $this->variacaoPercentual($atual['numeroReservas'], $anterior['numeroReservas']),
                'ticketMedio' => $this->variacaoPercentual($atual['ticketMedio'], $anterior['ticketMedio']),
            ],
        ];
    }

    /**
     * @return array{comissaoTotal: float, volumeTotal: float, numeroReservas: int, ticketMedio: float}
     */
    private function resumoPeriodo(CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $reservas = $this->reservasFaturaveisEntre($inicio, $fim)->get(['comissao_valor_centavos', 'valor_total_centavos']);

        $comissaoCentavos = (int) $reservas->sum('comissao_valor_centavos');
        $volumeCentavos = (int) $reservas->sum('valor_total_centavos');
        $numeroReservas = $reservas->count();

        return [
            'comissaoTotal' => $comissaoCentavos / 100.0,
            'volumeTotal' => $volumeCentavos / 100.0,
            'numeroReservas' => $numeroReservas,
            'ticketMedio' => $numeroReservas > 0 ? ($volumeCentavos / 100.0) / $numeroReservas : 0.0,
        ];
    }

    /**
     * Variação percentual do valor atual em relação ao anterior. Retorna
     * null quando o período anterior for zero (não dá pra calcular "%").
     */
    private function variacaoPercentual(float $atual, float $anterior): ?float
    {
        if ($anterior == 0.0) {
            return null;
        }

        return round((($atual - $anterior) / $anterior) * 100, 1);
    }

    /**
     * Comissão ao longo do tempo no período selecionado, agrupada por dia
     * (períodos de até 31 dias) ou por mês (períodos mais longos).
     *
     * @return list<array{label: string, valor: float, percentual: float}>
     */
    #[Computed]
    public function graficoComissao(): array
    {
        [$inicio, $fim] = $this->intervalo();

        $porMes = $inicio->diffInDays($fim) > 31;

        $reservas = $this->reservasFaturaveisEntre($inicio, $fim)
            ->get(['data', 'comissao_valor_centavos'])
            ->groupBy(fn (Reserva $reserva) => $porMes ? $reserva->data->format('Y-m') : $reserva->data->format('Y-m-d'));

        $buckets = [];
        $cursor = $inicio->clone();

        while ($cursor->lte($fim)) {
            $chave = $porMes ? $cursor->format('Y-m') : $cursor->format('Y-m-d');

            $buckets[$chave] = [
                'label' => $porMes ? $cursor->translatedFormat('M/y') : $cursor->format('d/m'),
                'valor' => ((int) $reservas->get($chave, collect())->sum('comissao_valor_centavos')) / 100.0,
            ];

            $cursor = $porMes ? $cursor->addMonth() : $cursor->addDay();
        }

        $maximo = collect($buckets)->max('valor') ?: 1.0;

        return collect($buckets)
            ->map(fn (array $linha) => $linha + ['percentual' => min(100.0, ($linha['valor'] / $maximo) * 100)])
            ->values()
            ->all();
    }

    /**
     * Quadras que mais geraram comissão no período, do maior para o menor.
     *
     * @return list<array{quadra: Quadra, comissao: float, percentual: float}>
     */
    #[Computed]
    public function rankingQuadras(): array
    {
        [$inicio, $fim] = $this->intervalo();

        $linhas = $this->reservasFaturaveisEntre($inicio, $fim)
            ->with('quadra.dono')
            ->get()
            ->groupBy('quadra_id')
            ->map(fn (Collection $reservas) => [
                'quadra' => $reservas->first()->quadra,
                'comissao' => ((int) $reservas->sum('comissao_valor_centavos')) / 100.0,
            ])
            ->filter(fn (array $linha) => $linha['quadra'] !== null)
            ->sortByDesc('comissao')
            ->take(5)
            ->values();

        $maximo = (float) $linhas->max('comissao') ?: 1.0;

        return $linhas
            ->map(fn (array $linha) => $linha + ['percentual' => min(100.0, ($linha['comissao'] / $maximo) * 100)])
            ->all();
    }

    /**
     * Horários de início mais frequentes entre as reservas faturáveis do período.
     *
     * @return list<array{horario: string, quantidade: int, percentual: float}>
     */
    #[Computed]
    public function rankingHorarios(): array
    {
        [$inicio, $fim] = $this->intervalo();

        $linhas = $this->reservasFaturaveisEntre($inicio, $fim)
            ->get(['hora_inicio'])
            ->groupBy(fn (Reserva $reserva) => substr((string) $reserva->hora_inicio, 0, 5))
            ->map(fn (Collection $reservas, string $horario) => ['horario' => $horario, 'quantidade' => $reservas->count()])
            ->sortByDesc('quantidade')
            ->take(5)
            ->values();

        $maximo = (int) $linhas->max('quantidade') ?: 1;

        return $linhas
            ->map(fn (array $linha) => $linha + ['percentual' => min(100.0, ($linha['quantidade'] / $maximo) * 100)])
            ->all();
    }

    /**
     * Dias da semana mais frequentes entre as reservas faturáveis do período.
     *
     * @return list<array{dia: string, quantidade: int, percentual: float}>
     */
    #[Computed]
    public function rankingDiasSemana(): array
    {
        [$inicio, $fim] = $this->intervalo();

        $nomes = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo'];

        $linhas = $this->reservasFaturaveisEntre($inicio, $fim)
            ->get(['data'])
            ->groupBy(fn (Reserva $reserva) => $reserva->data->dayOfWeekIso)
            ->map(fn (Collection $reservas, int $dia) => ['dia' => $nomes[$dia], 'quantidade' => $reservas->count()])
            ->sortByDesc('quantidade')
            ->values();

        $maximo = (int) $linhas->max('quantidade') ?: 1;

        return $linhas
            ->map(fn (array $linha) => $linha + ['percentual' => min(100.0, ($linha['quantidade'] / $maximo) * 100)])
            ->all();
    }

    /**
     * Últimas reservas feitas na plataforma, independente do status.
     */
    #[Computed]
    public function ultimasReservas(): Collection
    {
        return Reserva::query()
            ->with(['quadra', 'user'])
            ->latest('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * Cadastros (proprietários + quadras) aguardando decisão agora, e o
     * tempo médio histórico entre o envio e a decisão (aprovado/rejeitado).
     *
     * @return array{total: int, tempoMedioAnalise: ?string}
     */
    #[Computed]
    public function cadastrosPendentes(): array
    {
        $statusFila = [StatusCadastro::Pendente, StatusCadastro::EmAnalise];

        $totalProprietarios = User::query()->where('role', UserRole::DonoQuadra)->whereIn('status_cadastro', $statusFila)->count();
        $totalQuadras = Quadra::query()->whereIn('status_cadastro', $statusFila)->count();

        return [
            'total' => $totalProprietarios + $totalQuadras,
            'tempoMedioAnalise' => $this->tempoMedioAnalise(),
        ];
    }

    /**
     * Tempo médio, em todo o histórico, entre um cadastro ser enviado (ou
     * reenviado) e receber uma decisão final (aprovado ou rejeitado).
     */
    private function tempoMedioAnalise(): ?string
    {
        $eventos = CadastroHistorico::query()
            ->whereIn('evento', ['enviado', 'reenviado', 'aprovado', 'rejeitado'])
            ->orderBy('created_at')
            ->get(['avaliavel_type', 'avaliavel_id', 'evento', 'created_at']);

        $duracoesHoras = [];

        foreach ($eventos->groupBy(fn (CadastroHistorico $e) => $e->avaliavel_type.'-'.$e->avaliavel_id) as $grupo) {
            $inicio = null;

            foreach ($grupo as $evento) {
                if (in_array($evento->evento, ['enviado', 'reenviado'], true)) {
                    $inicio = $evento->created_at;
                } elseif (in_array($evento->evento, ['aprovado', 'rejeitado'], true) && $inicio) {
                    $duracoesHoras[] = $inicio->diffInMinutes($evento->created_at) / 60;
                    $inicio = null;
                }
            }
        }

        if ($duracoesHoras === []) {
            return null;
        }

        $mediaHoras = array_sum($duracoesHoras) / count($duracoesHoras);

        return $mediaHoras < 1
            ? round($mediaHoras * 60).' min'
            : number_format($mediaHoras, 1, ',', '.').' h';
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
