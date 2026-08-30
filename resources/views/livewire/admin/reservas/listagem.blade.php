<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Reservas') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Todas as reservas feitas na plataforma.') }}</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-orange-500 bg-white p-4">
            <flux:text>{{ __('Reservas no período filtrado') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->resumo['quantidade'] }}</div>
        </div>

        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-green-500 bg-white p-4">
            <flux:text>{{ __('Valor estimado (confirmadas)') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">R$ {{ number_format($this->resumo['valorTotalConfirmadas'], 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5">
        <flux:select wire:model.live="quadraId" :label="__('Quadra')" class="rounded-full">
            <flux:select.option value="">{{ __('Todas as quadras') }}</flux:select.option>
            @foreach ($this->quadras as $quadra)
                <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="donoId" :label="__('Dono')" class="rounded-full">
            <flux:select.option value="">{{ __('Todos os donos') }}</flux:select.option>
            @foreach ($this->donos as $dono)
                <flux:select.option value="{{ $dono->id }}">{{ $dono->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="status" :label="__('Status')" class="rounded-full">
            <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
            @foreach ($statusDisponiveis as $opcao)
                <flux:select.option value="{{ $opcao->value }}">{{ $opcao->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live="dataInicio" type="date" :label="__('De')" class="rounded-full" />

        <flux:input wire:model.live="dataFim" type="date" :label="__('Até')" class="rounded-full" />
    </div>

    @if ($this->reservas->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.calendar-days class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma reserva encontrada') }}</flux:heading>
            <flux:text>{{ __('Ajuste os filtros para ver outras reservas.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                    <flux:table.column>{{ __('Dono') }}</flux:table.column>
                    <flux:table.column>{{ __('Jogador') }}</flux:table.column>
                    <flux:table.column>{{ __('Data/Horário') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Pagamento') }}</flux:table.column>
                    <flux:table.column>{{ __('Valor') }}</flux:table.column>
                    <flux:table.column>{{ __('Ações') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->reservas as $reserva)
                        <flux:table.row wire:key="reserva-{{ $reserva->id }}">
                            <flux:table.cell class="font-semibold text-zinc-900">{{ $reserva->quadra->nome }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $reserva->quadra->dono->name }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $reserva->nome_cliente }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">
                                {{ $reserva->data->format('d/m/Y') }}
                                &middot; {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ match ($reserva->status) {
                                    App\Enums\ReservaStatus::Confirmada => 'green',
                                    App\Enums\ReservaStatus::Pendente => 'amber',
                                    App\Enums\ReservaStatus::Cancelada => 'red',
                                } }}" size="sm">
                                    {{ $reserva->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-500">
                                {{ $reserva->metodo_pagamento ? ucfirst($reserva->metodo_pagamento) : '—' }}
                                @if ($reserva->cancelamento_tipo)
                                    <span class="block text-xs text-zinc-400">{{ __('Cancelada:') }} {{ $reserva->cancelamento_tipo }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($reserva->quadra->valor_hora, 2, ',', '.') }}</flux:table.cell>
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
