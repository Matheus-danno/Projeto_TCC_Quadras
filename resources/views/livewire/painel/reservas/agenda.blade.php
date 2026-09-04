<div class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Agenda') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Veja a ocupação das suas quadras por dia.') }}</flux:text>
        </div>

        <flux:button :href="route('painel.reservas')" variant="outline" class="rounded-2xl !border-orange-300 !text-orange-600 hover:!bg-orange-50" wire:navigate>
            {{ __('Voltar') }}
        </flux:button>
    </div>

    <flux:card class="rounded-2xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <flux:button size="sm" variant="outline" class="rounded-full" icon="chevron-left" wire:click="mudarMes(-1)" />
                <flux:heading size="lg" class="min-w-40 text-center">
                    {{ \Illuminate\Support\Carbon::parse($mesAtual)->translatedFormat('F Y') }}
                </flux:heading>
                <flux:button size="sm" variant="outline" class="rounded-full" icon="chevron-right" wire:click="mudarMes(1)" />
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-zinc-500">
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-green-500"></span>{{ __('Confirmada') }}</span>
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-amber-500"></span>{{ __('Pendente') }}</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs font-semibold text-zinc-400">
            @foreach ([__('Dom'), __('Seg'), __('Ter'), __('Qua'), __('Qui'), __('Sex'), __('Sáb')] as $diaSemana)
                <div class="py-2">{{ $diaSemana }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-1">
            @foreach ($this->semanas as $semana)
                @foreach ($semana as $dia)
                    <button
                        type="button"
                        wire:click="selecionarDia('{{ $dia['data']->toDateString() }}')"
                        wire:key="dia-{{ $dia['data']->toDateString() }}"
                        class="flex h-16 flex-col items-center justify-start gap-1 rounded-xl border pt-1.5 text-sm transition
                            {{ $dia['data']->toDateString() === $diaSelecionado ? 'border-orange-500' : 'border-zinc-100 hover:bg-zinc-50' }}
                            {{ $dia['noMes'] ? 'text-zinc-900' : 'text-zinc-300' }}"
                    >
                        <span>{{ $dia['data']->day }}</span>
                        <span class="flex items-center gap-1">
                            @if ($dia['confirmadas'] > 0)
                                <span class="size-1.5 rounded-full bg-green-500"></span>
                            @endif
                            @if ($dia['pendentes'] > 0)
                                <span class="size-1.5 rounded-full bg-amber-500"></span>
                            @endif
                        </span>
                    </button>
                @endforeach
            @endforeach
        </div>
    </flux:card>

    <flux:card class="rounded-2xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg">{{ __('Grade de horários') }}</flux:heading>
                <flux:text class="text-zinc-400">
                    {{ \Illuminate\Support\Carbon::parse($diaSelecionado)->format('d/m/Y') }}
                </flux:text>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-zinc-500">
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm border border-green-300 bg-green-100"></span>{{ __('Confirmada') }}</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm border border-amber-300 bg-amber-100"></span>{{ __('Pendente') }}</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm border border-zinc-200"></span>{{ __('Livre') }}</span>
            </div>
        </div>

        @if (empty($this->gradeHorarios))
            <flux:text class="mt-4 text-zinc-400">{{ __('Cadastre uma quadra para ver a grade de horários.') }}</flux:text>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full border-separate border-spacing-1">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-white"></th>
                            @foreach ($this->quadras as $quadra)
                                <th class="min-w-32 px-1 pb-1 text-left text-xs font-semibold text-zinc-500">{{ $quadra->nome }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->gradeHorarios as $linha)
                            <tr wire:key="linha-{{ $linha['inicio'] }}">
                                <td class="sticky left-0 z-10 bg-white px-1 text-xs font-semibold whitespace-nowrap text-zinc-500">
                                    {{ $linha['inicio'] }}
                                </td>
                                @foreach ($linha['celulas'] as $celula)
                                    <td class="px-1 py-0.5">
                                        @if ($celula['ocupado'])
                                            <div
                                                class="rounded-lg border px-2 py-1.5 text-center text-xs
                                                    @if ($celula['status'] === App\Enums\ReservaStatus::Confirmada) border-green-300 bg-green-50 text-green-700
                                                    @else border-amber-300 bg-amber-50 text-amber-700 @endif"
                                                title="{{ $celula['cliente'] }}"
                                            >
                                                {{ \Illuminate\Support\Str::limit($celula['cliente'], 12) }}
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="abrirAgendamento({{ $celula['quadraId'] }}, '{{ $linha['inicio'] }}')"
                                                class="w-full rounded-lg border border-zinc-200 px-2 py-1.5 text-center text-xs text-zinc-400 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600"
                                            >
                                                {{ __('Livre') }}
                                            </button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:card>

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">{{ \Illuminate\Support\Carbon::parse($diaSelecionado)->translatedFormat('d \d\e F \d\e Y') }}</flux:heading>

        @if ($this->reservasDoDia->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-12 text-center">
                <flux:icon.calendar-days class="size-8 text-zinc-300" />
                <flux:text>{{ __('Nenhuma reserva neste dia.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="flex flex-col divide-y divide-zinc-100 rounded-2xl p-0">
                @foreach ($this->reservasDoDia as $reserva)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3" wire:key="reserva-dia-{{ $reserva->id }}">
                        <div>
                            <flux:heading size="sm">{{ $reserva->nome_cliente }}</flux:heading>
                            <flux:text class="text-zinc-400">
                                {{ $reserva->quadra->nome }} - {{ substr($reserva->hora_inicio, 0, 5) }}-{{ substr($reserva->hora_fim, 0, 5) }}
                            </flux:text>
                        </div>

                        <flux:badge color="{{ match ($reserva->status) {
                            App\Enums\ReservaStatus::Confirmada => 'green',
                            App\Enums\ReservaStatus::Pendente => 'amber',
                            App\Enums\ReservaStatus::Cancelada => 'red',
                        } }}" size="sm">
                            {{ $reserva->status->label() }}
                        </flux:badge>
                    </div>
                @endforeach
            </flux:card>
        @endif
    </div>

    <flux:modal name="agendar-horario" class="w-full md:w-96">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Realizar agendamento') }}</flux:heading>

            <flux:text>
                {{ $this->quadras->firstWhere('id', $slotQuadraId)?->nome }}<br>
                {{ \Illuminate\Support\Carbon::parse($diaSelecionado)->format('d/m/Y') }} - {{ $slotHorario }}
            </flux:text>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                        {{ __('Cancelar') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    variant="primary"
                    color="orange"
                    class="rounded-full"
                    :href="route('painel.agendamento-manual', ['quadraId' => $slotQuadraId, 'data' => $diaSelecionado, 'horaInicio' => $slotHorario])"
                    wire:navigate
                >
                    {{ __('Realizar agendamento') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
