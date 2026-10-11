<div class="flex w-full flex-col gap-6" wire:loading.class="opacity-60" wire:target="periodo,inicioPersonalizado,fimPersonalizado">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Visão geral da plataforma e da comissão do AlugaQuadra.') }}</flux:text>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <flux:select wire:model.live="periodo" class="rounded-full sm:w-48">
                <flux:select.option value="hoje">{{ __('Hoje') }}</flux:select.option>
                <flux:select.option value="7dias">{{ __('Últimos 7 dias') }}</flux:select.option>
                <flux:select.option value="mes_atual">{{ __('Este mês') }}</flux:select.option>
                <flux:select.option value="mes_anterior">{{ __('Mês anterior') }}</flux:select.option>
                <flux:select.option value="ano_atual">{{ __('Este ano') }}</flux:select.option>
                <flux:select.option value="personalizado">{{ __('Intervalo personalizado') }}</flux:select.option>
            </flux:select>

            @if ($periodo === 'personalizado')
                <flux:input type="date" wire:model.live="inicioPersonalizado" class="rounded-full" />
                <flux:input type="date" wire:model.live="fimPersonalizado" class="rounded-full" />
            @endif
        </div>
    </div>

    @php
        $variacaoBadge = function (?float $percentual) {
            if ($percentual === null) {
                return ['texto' => __('sem período anterior'), 'cor' => 'text-zinc-400'];
            }

            $sinal = $percentual > 0 ? '+' : '';

            return [
                'texto' => "{$sinal}".number_format($percentual, 1, ',', '.').'% '.__('vs. período anterior'),
                'cor' => $percentual >= 0 ? 'text-green-600' : 'text-red-500',
            ];
        };
    @endphp

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Comissão total') }}</flux:text>
            <flux:heading size="xl">R$ {{ number_format($this->indicadores['comissaoTotal'], 2, ',', '.') }}</flux:heading>
            <flux:text class="text-xs {{ $variacaoBadge($this->indicadores['variacao']['comissaoTotal'])['cor'] }}">
                {{ $variacaoBadge($this->indicadores['variacao']['comissaoTotal'])['texto'] }}
            </flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Volume movimentado') }}</flux:text>
            <flux:heading size="xl">R$ {{ number_format($this->indicadores['volumeTotal'], 2, ',', '.') }}</flux:heading>
            <flux:text class="text-xs {{ $variacaoBadge($this->indicadores['variacao']['volumeTotal'])['cor'] }}">
                {{ $variacaoBadge($this->indicadores['variacao']['volumeTotal'])['texto'] }}
            </flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Número de reservas') }}</flux:text>
            <flux:heading size="xl">{{ $this->indicadores['numeroReservas'] }}</flux:heading>
            <flux:text class="text-xs {{ $variacaoBadge($this->indicadores['variacao']['numeroReservas'])['cor'] }}">
                {{ $variacaoBadge($this->indicadores['variacao']['numeroReservas'])['texto'] }}
            </flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Ticket médio') }}</flux:text>
            <flux:heading size="xl">R$ {{ number_format($this->indicadores['ticketMedio'], 2, ',', '.') }}</flux:heading>
            <flux:text class="text-xs {{ $variacaoBadge($this->indicadores['variacao']['ticketMedio'])['cor'] }}">
                {{ $variacaoBadge($this->indicadores['variacao']['ticketMedio'])['texto'] }}
            </flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Quadras ativas') }}</flux:text>
            <flux:heading size="xl">{{ $this->indicadores['quadrasAtivas'] }}</flux:heading>
            <flux:text class="text-xs text-zinc-400">{{ __('no catálogo agora') }}</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Usuários cadastrados') }}</flux:text>
            <flux:heading size="xl">{{ $this->indicadores['usuariosCadastrados'] }}</flux:heading>
            <flux:text class="text-xs text-zinc-400">{{ __('clientes e donos') }}</flux:text>
        </flux:card>
    </div>

    <a href="{{ route('admin.aprovacoes') }}" wire:navigate class="block">
        <flux:card class="flex flex-col gap-1 rounded-2xl p-5 transition hover:bg-zinc-50">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:text class="text-zinc-400">{{ __('Cadastros pendentes de aprovação') }}</flux:text>
                    <flux:heading size="xl" class="{{ $this->cadastrosPendentes['total'] > 0 ? 'text-orange-600' : '' }}">
                        {{ $this->cadastrosPendentes['total'] }}
                    </flux:heading>
                    <flux:text class="text-xs text-zinc-400">
                        {{ $this->cadastrosPendentes['tempoMedioAnalise'] ? __('tempo médio de análise: :tempo', ['tempo' => $this->cadastrosPendentes['tempoMedioAnalise']]) : __('ainda sem histórico de decisões') }}
                    </flux:text>
                </div>
                <flux:icon.inbox class="size-8 text-orange-400" />
            </div>
        </flux:card>
    </a>

    <flux:card class="flex flex-col gap-4 rounded-2xl">
        <flux:heading size="lg">{{ __('Comissão ao longo do tempo') }}</flux:heading>

        @if (empty($this->graficoComissao))
            <div class="flex flex-col items-center gap-2 py-10 text-center">
                <flux:icon.chart-bar class="size-8 text-zinc-300" />
                <flux:text>{{ __('Sem comissão registrada nesse período.') }}</flux:text>
            </div>
        @else
            <div class="flex h-40 items-end gap-2 overflow-x-auto">
                @foreach ($this->graficoComissao as $ponto)
                    <div
                        class="flex h-full min-w-6 flex-1 flex-col items-center justify-end gap-1.5"
                        title="{{ $ponto['label'] }}: R$ {{ number_format($ponto['valor'], 2, ',', '.') }}"
                    >
                        <div class="w-full rounded-t-md bg-orange-500" style="height: {{ $ponto['percentual'] }}%; min-height: 2px"></div>
                        <span class="text-xs text-zinc-500">{{ $ponto['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-3 rounded-2xl">
            <flux:heading size="lg">{{ __('Quadras que mais geram comissão') }}</flux:heading>

            @if (empty($this->rankingQuadras))
                <flux:text class="text-zinc-500">{{ __('Nenhuma comissão registrada nesse período.') }}</flux:text>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($this->rankingQuadras as $linha)
                        <div wire:key="ranking-quadra-{{ $linha['quadra']->id }}">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-zinc-900">{{ $linha['quadra']->nome }}</span>
                                <span class="text-zinc-500">R$ {{ number_format($linha['comissao'], 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ $linha['percentual'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>

        <flux:card class="flex flex-col gap-3 rounded-2xl">
            <flux:heading size="lg">{{ __('Horários mais alugados') }}</flux:heading>

            @if (empty($this->rankingHorarios))
                <flux:text class="text-zinc-500">{{ __('Nenhuma reserva nesse período.') }}</flux:text>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($this->rankingHorarios as $linha)
                        <div wire:key="ranking-horario-{{ $linha['horario'] }}">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-zinc-900">{{ $linha['horario'] }}</span>
                                <span class="text-zinc-500">{{ $linha['quantidade'] }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ $linha['percentual'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>

        <flux:card class="flex flex-col gap-3 rounded-2xl">
            <flux:heading size="lg">{{ __('Dias da semana mais alugados') }}</flux:heading>

            @if (empty($this->rankingDiasSemana))
                <flux:text class="text-zinc-500">{{ __('Nenhuma reserva nesse período.') }}</flux:text>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($this->rankingDiasSemana as $linha)
                        <div wire:key="ranking-dia-{{ $linha['dia'] }}">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-zinc-900">{{ $linha['dia'] }}</span>
                                <span class="text-zinc-500">{{ $linha['quantidade'] }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ $linha['percentual'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <flux:heading size="lg" class="p-6 pb-0">{{ __('Últimas reservas') }}</flux:heading>

        @if ($this->ultimasReservas->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:icon.calendar-days class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma reserva ainda') }}</flux:heading>
                <flux:text>{{ __('As reservas feitas na plataforma vão aparecer aqui.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Data') }}</flux:table.column>
                    <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                    <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->ultimasReservas as $reserva)
                        <flux:table.row wire:key="ultima-reserva-{{ $reserva->id }}">
                            <flux:table.cell class="ps-6! text-zinc-500">{{ $reserva->data->format('d/m/Y') }} {{ substr($reserva->hora_inicio, 0, 5) }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">{{ $reserva->quadra->nome }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $reserva->nome_cliente }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ match ($reserva->status) {
                                    App\Enums\ReservaStatus::Confirmada => 'green',
                                    App\Enums\ReservaStatus::Cancelada => 'red',
                                    default => 'orange',
                                } }}">{{ $reserva->status->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
