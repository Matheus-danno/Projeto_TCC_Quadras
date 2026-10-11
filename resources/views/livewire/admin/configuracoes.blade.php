<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Configurações') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Porcentagem de comissão do admin sobre cada reserva.') }}</flux:text>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl">
        <div>
            <flux:heading size="lg">{{ __('Comissão sobre reservas') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                {{ __('A mudança vale só para reservas confirmadas e pagas a partir de agora — o histórico não é recalculado.') }}
            </flux:text>
        </div>

        <form wire:submit="salvar" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:input wire:model="percentual" type="number" step="0.01" min="0" max="100" :label="__('Percentual (%)')" class="sm:w-48" />
            <flux:button type="submit" variant="primary" color="orange" class="rounded-full">{{ __('Salvar') }}</flux:button>
        </form>
    </flux:card>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <flux:heading size="lg" class="p-6 pb-0">{{ __('Histórico de alterações') }}</flux:heading>

        @if ($this->historico->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:text class="text-zinc-500">{{ __('Nenhuma alteração registrada ainda.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Data') }}</flux:table.column>
                    <flux:table.column>{{ __('Percentual') }}</flux:table.column>
                    <flux:table.column>{{ __('Alterado por') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->historico as $configuracao)
                        <flux:table.row wire:key="comissao-config-{{ $configuracao->id }}">
                            <flux:table.cell class="ps-6! text-zinc-500">{{ $configuracao->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">{{ number_format($configuracao->percentual, 2, ',', '.') }}%</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $configuracao->alteradoPor?->name ?? __('Sistema (padrão)') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
