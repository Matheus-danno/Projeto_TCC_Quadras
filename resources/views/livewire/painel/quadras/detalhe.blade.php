<div class="flex w-full flex-col gap-6">
    <div class="flex flex-col gap-3">
        <flux:link :href="route('painel.quadras')" class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-orange-600! no-underline hover:underline" wire:navigate>
            {{ __('← Voltar para minhas quadras') }}
        </flux:link>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ $quadra->nome }}</flux:heading>
                <flux:badge color="{{ $quadra->ativa ? 'green' : 'red' }}" size="sm">
                    {{ $quadra->ativa ? __('Ativa') : __('Inativa') }}
                </flux:badge>
            </div>

            <flux:button :href="route('painel.quadras.editar', $quadra)" variant="primary" color="orange" class="rounded-xl" wire:navigate>
                {{ __('Editar Quadra') }}
            </flux:button>
        </div>
        <flux:text class="text-base text-zinc-400">
            {{ $quadra->esporte->label() }} - {{ $quadra->cobertura ? __('Coberta') : __('Descoberta') }}
        </flux:text>
    </div>

    @if ($quadra->fotos->isNotEmpty())
        <div class="flex gap-3 overflow-x-auto pb-1">
            @foreach ($quadra->fotos as $foto)
                <img
                    src="{{ $foto->url() }}"
                    alt="{{ $quadra->nome }}"
                    class="h-48 w-64 shrink-0 rounded-2xl object-cover"
                >
            @endforeach
        </div>
    @endif

    <flux:card class="flex flex-wrap gap-6 rounded-2xl">
        <div>
            <flux:text class="text-xs text-zinc-400">{{ __('Endereço') }}</flux:text>
            <flux:heading size="md">{{ $quadra->endereco }} — {{ $quadra->bairro }}, {{ $quadra->cidade }}</flux:heading>
        </div>

        <div>
            <flux:text class="text-xs text-zinc-400">{{ __('Valor/Hora') }}</flux:text>
            <flux:heading size="md" class="text-orange-500!">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</flux:heading>
        </div>

        @if ($quadra->descricao)
            <div class="w-full">
                <flux:text class="text-xs text-zinc-400">{{ __('Descrição') }}</flux:text>
                <flux:text>{{ $quadra->descricao }}</flux:text>
            </div>
        @endif
    </flux:card>

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">{{ __('Reservas recentes') }}</flux:heading>

        @if ($this->reservasRecentes->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.calendar-days class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma reserva encontrada') }}</flux:heading>
                <flux:text>{{ __('Esta quadra ainda não recebeu reservas.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="rounded-2xl p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Data/Horário') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->reservasRecentes as $reserva)
                            <flux:table.row wire:key="reserva-{{ $reserva->id }}">
                                <flux:table.cell class="font-semibold text-zinc-900">{{ $reserva->nome_cliente }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">
                                    {{ $reserva->data->format('d/m/Y') }}, {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}
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
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif
    </div>
</div>
