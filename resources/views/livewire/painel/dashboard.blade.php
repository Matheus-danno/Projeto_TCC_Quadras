<div class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Meu Painel') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Gerêncie suas quadras, reservas e faturamento em um só lugar.') }}</flux:text>
        </div>

        <flux:button :href="route('painel.quadras')" variant="primary" color="orange" icon="plus" class="rounded-2xl" wire:navigate>
            {{ __('Cadastrar Quadra') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="rounded-2xl border-l-4 border-orange-500">
            <flux:text class="text-zinc-400">{{ __('Quadras cadastradas') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->indicadores['quantidadeQuadras'] }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-green-500">
            <flux:text class="text-zinc-400">{{ __('Reservas no mês') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->indicadores['reservasDoMes'] }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-orange-500">
            <flux:text class="text-zinc-400">{{ __('Faturamento do mês') }}</flux:text>
            <flux:heading size="xl" class="mt-1">R$ {{ number_format($this->indicadores['faturamentoDoMes'], 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="rounded-2xl border-l-4 border-green-500">
            <flux:text class="text-zinc-400">{{ __('Avaliação média') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->indicadores['avaliacaoMedia'] }}</flux:heading>
        </flux:card>
    </div>

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:heading size="lg">{{ __('Minhas Quadras') }}</flux:heading>
            <flux:button :href="route('painel.quadras')" variant="ghost" class="rounded-full" wire:navigate>
                {{ __('Ver todas') }}
            </flux:button>
        </div>

        @if ($this->quadras->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.map-pin class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma quadra cadastrada ainda') }}</flux:heading>
                <flux:text>{{ __('Cadastre a primeira quadra para começar a receber reservas.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Nome') }}</flux:table.column>
                        <flux:table.column>{{ __('Esporte') }}</flux:table.column>
                        <flux:table.column>{{ __('Valor/Hora') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Reservas') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->quadras as $quadra)
                            <flux:table.row wire:key="quadra-{{ $quadra->id }}">
                                <flux:table.cell class="font-semibold text-zinc-900">{{ $quadra->nome }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="orange" size="sm">{{ $quadra->esporte->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="{{ $quadra->cobertura ? 'green' : 'zinc' }}" size="sm">
                                        {{ $quadra->cobertura ? __('Coberta') : __('Descoberta') }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $quadra->reservas_count }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif
    </div>
</div>
