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
                <flux:heading size="lg">{{ __('Horários disponíveis') }}</flux:heading>
                <flux:text class="text-zinc-400">
                    {{ $this->quadras->firstWhere('id', (int) $quadraId)?->nome }} - {{ \Illuminate\Support\Carbon::parse($diaSelecionado)->format('d/m/Y') }}
                </flux:text>
            </div>

            <flux:select wire:model.live="quadraId" class="w-56 rounded-full">
                @foreach ($this->quadras as $quadra)
                    <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        @if (empty($this->horariosDoDia))
            <flux:text class="mt-4 text-zinc-400">{{ __('Cadastre uma quadra para ver os horários.') }}</flux:text>
        @else
            <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-7">
                @foreach ($this->horariosDoDia as $horario)
                    <div
                        class="rounded-full border px-3 py-1.5 text-center text-sm
                            {{ $horario['ocupado'] ? 'border-zinc-300 bg-zinc-200 text-zinc-500' : 'border-zinc-200 text-zinc-700' }}"
                    >
                        {{ $horario['inicio'] }}
                    </div>
                @endforeach
            </div>

            <div class="mt-3 flex items-center gap-4 text-sm text-zinc-500">
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-zinc-300"></span>{{ __('Ocupado') }}</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm border border-zinc-300"></span>{{ __('Livre') }}</span>
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
</div>
