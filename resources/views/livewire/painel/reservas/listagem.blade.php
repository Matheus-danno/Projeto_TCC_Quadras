<div class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Reservas') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Acompanhe todas as reservas feitas nas suas quadras.') }}</flux:text>
        </div>

        <flux:tooltip :content="__('Em breve')" position="bottom">
            <flux:button icon="calendar-days" variant="outline" class="rounded-2xl cursor-not-allowed opacity-50" aria-disabled="true" tabindex="-1">
                {{ __('Agenda') }}
            </flux:button>
        </flux:tooltip>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ([
            'todas' => __('Todas'),
            'hoje' => __('Hoje'),
            'semana' => __('Esta Semana'),
            'pendentes' => __('Pendentes'),
            'concluidas' => __('Concluídas'),
        ] as $valor => $rotulo)
            <button
                type="button"
                wire:click="$set('aba', '{{ $valor }}')"
                class="rounded-2xl px-4 py-2 text-sm font-semibold transition {{ $aba === $valor ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
            >
                {{ $rotulo }}
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="rounded-2xl border-l-4 border-orange-500">
            <flux:text class="text-zinc-400">{{ __('Reservas hoje') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->resumo['hoje'] }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-green-500">
            <flux:text class="text-zinc-400">{{ __('Reservas na semana') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->resumo['semana'] }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-amber-500">
            <flux:text class="text-zinc-400">{{ __('Pendentes') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->resumo['pendentes'] }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-orange-500">
            <flux:text class="text-zinc-400">{{ __('Faturamento da semana') }}</flux:text>
            <flux:heading size="xl" class="mt-1">R$ {{ number_format($this->resumo['faturamentoSemana'], 2, ',', '.') }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <flux:select wire:model.live="quadraId" :label="__('Quadra')" class="rounded-full">
            <flux:select.option value="">{{ __('Todas as quadras') }}</flux:select.option>
            @foreach ($this->quadras as $quadra)
                <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live="data" type="date" :label="__('Data')" class="rounded-full" />

        <flux:select wire:model.live="status" :label="__('Status')" class="rounded-full">
            <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
            @foreach ($statusDisponiveis as $opcao)
                <flux:select.option value="{{ $opcao->value }}">{{ $opcao->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->reservas->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.calendar-days class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma reserva encontrada') }}</flux:heading>
            <flux:text>{{ __('Ajuste os filtros ou aguarde novas reservas nas suas quadras.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="rounded-2xl p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                    <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                    <flux:table.column>{{ __('Data/Horário') }}</flux:table.column>
                    <flux:table.column>{{ __('Duração') }}</flux:table.column>
                    <flux:table.column>{{ __('Valor') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Ações') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->reservas as $reserva)
                        <flux:table.row wire:key="reserva-{{ $reserva->id }}">
                            <flux:table.cell class="font-semibold text-zinc-900">{{ $reserva->nome_cliente }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $reserva->quadra->nome }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">
                                {{ $reserva->data->format('d/m/Y') }}, {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-500">
                                @php
                                    $duracaoMinutos = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 60;
                                    $duracaoHoras = intdiv($duracaoMinutos, 60);
                                    $duracaoRestoMin = $duracaoMinutos % 60;
                                @endphp
                                {{ $duracaoHoras }}h{{ $duracaoRestoMin > 0 ? sprintf('%02d', $duracaoRestoMin) : '' }}
                            </flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($reserva->quadra->valor_hora, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ match ($reserva->status) {
                                    App\Enums\ReservaStatus::Confirmada => 'green',
                                    App\Enums\ReservaStatus::Pendente => 'amber',
                                    App\Enums\ReservaStatus::Cancelada => 'red',
                                } }}" size="sm">
                                    {{ $reserva->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($reserva->status === App\Enums\ReservaStatus::Pendente)
                                        <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="confirmar({{ $reserva->id }})">
                                            {{ __('Confirmar') }}
                                        </flux:button>
                                    @endif

                                    @if ($reserva->status !== App\Enums\ReservaStatus::Cancelada)
                                        <flux:button
                                            size="sm"
                                            variant="outline"
                                            class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50"
                                            wire:click="cancelar({{ $reserva->id }})"
                                        >
                                            {{ __('Cancelar') }}
                                        </flux:button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
</div>
