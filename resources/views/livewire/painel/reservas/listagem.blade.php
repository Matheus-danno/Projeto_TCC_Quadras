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

    <div class="flex flex-wrap items-center justify-between gap-4">
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

        <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <div>
                <flux:heading size="lg">{{ $this->resumo['hoje'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">{{ __('Hoje') }}</flux:text>
            </div>
            <div>
                <flux:heading size="lg">{{ $this->resumo['semana'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">{{ __('Esta Semana') }}</flux:text>
            </div>
            <div>
                <flux:heading size="lg">{{ $this->resumo['pendentes'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">{{ __('Pendentes') }}</flux:text>
            </div>
            <div>
                <flux:heading size="lg">R$ {{ number_format($this->resumo['faturamentoSemana'], 2, ',', '.') }}</flux:heading>
                <flux:text class="text-xs text-zinc-400">{{ __('Faturamento (semana)') }}</flux:text>
            </div>
        </div>
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
                    <flux:table.column class="ps-6!">{{ __('Cliente') }}</flux:table.column>
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
                            <flux:table.cell class="ps-6! font-semibold text-zinc-900">{{ $reserva->nome_cliente }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $reserva->quadra->nome }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">
                                {{ $reserva->data->format('d/m/Y') }}, {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $reserva->duracaoFormatada() }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">
                                @php
                                    $horas = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;
                                @endphp
                                R$ {{ number_format($reserva->quadra->valor_hora * $horas, 2, ',', '.') }}
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
                            <flux:table.cell>
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:button
                                        size="sm"
                                        variant="outline"
                                        class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                                        wire:click="verDetalhes({{ $reserva->id }})"
                                    >
                                        {{ __('Detalhes') }}
                                    </flux:button>

                                    @if ($reserva->status !== App\Enums\ReservaStatus::Cancelada)
                                        <flux:button
                                            size="sm"
                                            variant="outline"
                                            class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50"
                                            wire:click="pedirCancelamento({{ $reserva->id }})"
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

    <flux:modal name="detalhes-reserva" class="w-full md:w-[420px]">
        @if ($this->reservaSelecionada)
            @php $reserva = $this->reservaSelecionada; @endphp
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Detalhes da reserva') }}</flux:heading>

                <div class="flex items-center gap-3">
                    @if ($reserva->user)
                        <flux:avatar size="sm" :name="$reserva->user->name" :initials="$reserva->user->initials()" color="orange" />
                    @endif
                    <div>
                        <flux:heading size="sm">{{ $reserva->nome_cliente }}</flux:heading>
                        <flux:badge color="{{ match ($reserva->status) {
                            App\Enums\ReservaStatus::Confirmada => 'green',
                            App\Enums\ReservaStatus::Pendente => 'amber',
                            App\Enums\ReservaStatus::Cancelada => 'red',
                        } }}" size="sm">
                            {{ $reserva->status->label() }}
                        </flux:badge>
                    </div>
                </div>

                <div class="flex flex-col gap-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-zinc-400">{{ __('Quadra') }}</flux:text>
                        <flux:text class="text-right font-medium text-zinc-900">{{ $reserva->quadra->nome }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-zinc-400">{{ __('Endereço') }}</flux:text>
                        <flux:text class="text-right font-medium text-zinc-900">{{ $reserva->quadra->endereco }} - {{ $reserva->quadra->bairro }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-zinc-400">{{ __('Data / Horário') }}</flux:text>
                        <flux:text class="text-right font-medium text-zinc-900">
                            {{ $reserva->data->format('d/m/Y') }}, {{ substr($reserva->hora_inicio, 0, 5) }}-{{ substr($reserva->hora_fim, 0, 5) }}
                        </flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-zinc-400">{{ __('Duração') }}</flux:text>
                        <flux:text class="text-right font-medium text-zinc-900">{{ $reserva->duracaoFormatada() }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-zinc-400">{{ __('Valor') }}</flux:text>
                        @php $horas = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600; @endphp
                        <flux:text class="text-right font-medium text-zinc-900">R$ {{ number_format($reserva->quadra->valor_hora * $horas, 2, ',', '.') }}</flux:text>
                    </div>
                    @if ($reserva->metodo_pagamento)
                        <div class="flex items-center justify-between gap-3">
                            <flux:text class="text-zinc-400">{{ __('Pagamento') }}</flux:text>
                            <flux:text class="text-right font-medium text-zinc-900">
                                {{ App\Enums\FormaPagamento::tryFrom($reserva->metodo_pagamento)?->label() ?? $reserva->metodo_pagamento }}
                            </flux:text>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="text-zinc-400">{{ __('Código') }}</flux:text>
                        <flux:text class="text-right font-medium text-zinc-900">{{ $reserva->codigoReserva() }}</flux:text>
                    </div>
                </div>

                @if ($reserva->user?->telefone || $reserva->user?->email)
                    <div class="flex flex-col gap-1 border-t border-zinc-100 pt-3 text-sm text-zinc-500">
                        @if ($reserva->user->telefone)
                            <div class="flex items-center gap-2">
                                <flux:icon.phone variant="mini" />
                                {{ $reserva->user->telefone }}
                            </div>
                        @endif
                        @if ($reserva->user->email)
                            <div class="flex items-center gap-2">
                                <flux:icon.envelope variant="mini" />
                                {{ $reserva->user->email }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex flex-col gap-2 sm:flex-row">
                    <flux:modal.close>
                        <flux:button variant="outline" class="w-full rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                            {{ __('Fechar') }}
                        </flux:button>
                    </flux:modal.close>

                    @if ($reserva->status === App\Enums\ReservaStatus::Pendente)
                        <flux:button variant="primary" color="orange" class="w-full rounded-full" wire:click="confirmar({{ $reserva->id }})">
                            {{ __('Confirmar reserva') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal name="cancelar-reserva" class="w-full md:w-96">
        @if ($this->reservaSelecionada)
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Cancelar reserva?') }}</flux:heading>
                <flux:text>
                    {{ __('Tem certeza que deseja cancelar a reserva de :cliente para :quadra, em :data? Essa ação não pode ser desfeita.', [
                        'cliente' => $this->reservaSelecionada->nome_cliente,
                        'quadra' => $this->reservaSelecionada->quadra->nome,
                        'data' => $this->reservaSelecionada->data->format('d/m/Y'),
                    ]) }}
                </flux:text>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                            {{ __('Voltar') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" class="rounded-full" wire:click="cancelar">
                        {{ __('Confirmar cancelamento') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
