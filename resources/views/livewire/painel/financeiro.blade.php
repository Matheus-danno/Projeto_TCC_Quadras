<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Financeiro') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Relatório de faturamento a partir das reservas confirmadas nas suas quadras.') }}</flux:text>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:flex-wrap">
        <flux:select wire:model.live="periodo" :label="__('Período')" class="rounded-full md:w-64">
            <flux:select.option value="mes_atual">{{ __('Este mês') }}</flux:select.option>
            <flux:select.option value="mes_passado">{{ __('Mês passado') }}</flux:select.option>
            <flux:select.option value="personalizado">{{ __('Período personalizado') }}</flux:select.option>
        </flux:select>

        @if ($periodo === 'personalizado')
            <flux:input wire:model.live="dataInicio" type="date" :label="__('De')" class="rounded-full md:w-48" />
            <flux:input wire:model.live="dataFim" type="date" :label="__('Até')" class="rounded-full md:w-48" />
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:card class="rounded-2xl border-l-4 border-green-500">
            <div class="flex items-center justify-between gap-2">
                <flux:text class="text-zinc-500">{{ __('Faturamento no período') }}</flux:text>
                <flux:badge color="orange" size="sm">{{ $this->periodoFormatado() }}</flux:badge>
            </div>
            <flux:heading size="xl" class="mt-1">R$ {{ number_format($this->faturamento, 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-orange-500">
            <flux:text class="text-zinc-500">{{ __('Reservas confirmadas no período') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->reservas->total() }}</flux:heading>
        </flux:card>
    </div>

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">{{ __('Faturamento por quadra') }}</flux:heading>

        @if ($this->faturamentoPorQuadra->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.banknotes class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhum faturamento no período') }}</flux:heading>
                <flux:text>{{ __('Ajuste o período selecionado ou aguarde novas reservas confirmadas.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="rounded-2xl p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                        <flux:table.column>{{ __('Reservas') }}</flux:table.column>
                        <flux:table.column>{{ __('Faturamento') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->faturamentoPorQuadra as $linha)
                            <flux:table.row wire:key="quadra-{{ $linha['quadra']->id }}">
                                <flux:table.cell class="font-semibold text-zinc-900">{{ $linha['quadra']->nome }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $linha['reservas'] }}</flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($linha['faturamento'], 2, ',', '.') }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif
    </div>

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">{{ __('Reservas do período') }}</flux:heading>

        @if ($this->reservas->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.calendar-days class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma reserva confirmada no período') }}</flux:heading>
                <flux:text>{{ __('Ajuste o período selecionado.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="rounded-2xl p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                        <flux:table.column>{{ __('Data') }}</flux:table.column>
                        <flux:table.column>{{ __('Valor') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->reservas as $reserva)
                            <flux:table.row wire:key="reserva-{{ $reserva->id }}">
                                <flux:table.cell class="font-semibold text-zinc-900">{{ $reserva->nome_cliente }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $reserva->quadra->nome }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">
                                    {{ $reserva->data->format('d/m/Y') }}, {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}
                                </flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">
                                    R$ {{ number_format($reserva->quadra->valor_hora * $this->duracaoEmHoras($reserva), 2, ',', '.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="p-4">
                    <flux:pagination :paginator="$this->reservas" />
                </div>
            </flux:card>
        @endif
    </div>
</div>
