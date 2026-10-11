<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Reservas') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Todas as reservas da plataforma, cancelamentos e o impacto na comissão.') }}</flux:text>
    </div>

    <div class="flex gap-2">
        <flux:button size="sm" :variant="$aba === 'todas' ? 'primary' : 'outline'" :color="$aba === 'todas' ? 'orange' : null" class="rounded-full" wire:click="$set('aba', 'todas')">
            {{ __('Todas') }}
        </flux:button>
        <flux:button size="sm" :variant="$aba === 'canceladas' ? 'primary' : 'outline'" :color="$aba === 'canceladas' ? 'orange' : null" class="rounded-full" wire:click="$set('aba', 'canceladas')">
            {{ __('Canceladas e reembolsos') }}
        </flux:button>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex flex-col gap-3 p-6 pb-0 lg:flex-row lg:items-center lg:justify-between">
            <flux:heading size="lg">{{ __('Lista de reservas') }}</flux:heading>

            <div class="flex flex-wrap gap-2">
                <flux:input wire:model.live.debounce.400ms="busca" placeholder="{{ __('Buscar cliente ou quadra...') }}" class="rounded-full sm:w-56" />

                <flux:select wire:model.live="filtroQuadraId" class="rounded-full sm:w-40">
                    <flux:select.option value="">{{ __('Todas as quadras') }}</flux:select.option>
                    @foreach ($this->quadras as $quadra)
                        <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filtroStatus" class="rounded-full sm:w-36">
                    <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
                    @foreach ($statusDisponiveis as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div wire:loading.class="opacity-60" wire:target="busca,filtroQuadraId,filtroStatus,aba">
            @if ($this->reservas->isEmpty())
                <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                    <flux:icon.calendar-days class="size-8 text-zinc-300" />
                    <flux:heading size="lg">{{ __('Nenhuma reserva encontrada') }}</flux:heading>
                    <flux:text>{{ __('Ajuste a busca ou os filtros selecionados.') }}</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6!">{{ __('Data') }}</flux:table.column>
                        <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                        <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Impacto na comissão') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->reservas as $reserva)
                            @php $impacto = $this->impactoComissao($reserva); @endphp
                            <flux:table.row wire:key="admin-reserva-{{ $reserva->id }}">
                                <flux:table.cell class="ps-6! text-zinc-500">{{ $reserva->data->format('d/m/Y') }} {{ substr($reserva->hora_inicio, 0, 5) }}</flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">{{ $reserva->quadra->nome }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $reserva->nome_cliente }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ match ($reserva->status) {
                                        App\Enums\ReservaStatus::Confirmada => 'green',
                                        App\Enums\ReservaStatus::Cancelada => 'red',
                                        default => 'orange',
                                    } }}">{{ $reserva->status->label() }}</flux:badge>
                                    @if ($reserva->status === App\Enums\ReservaStatus::Cancelada && $reserva->motivo_cancelamento)
                                        <flux:text class="mt-1 block text-xs text-zinc-400">{{ $reserva->motivo_cancelamento }}</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($impacto['estornado'])
                                        <span class="text-red-500">- R$ {{ number_format($impacto['valor'], 2, ',', '.') }} {{ __('(estornado)') }}</span>
                                    @elseif ($impacto['valor'] > 0)
                                        <span class="text-green-600">R$ {{ number_format($impacto['valor'], 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="p-4">
                    <flux:pagination :paginator="$this->reservas" />
                </div>
            @endif
        </div>
    </flux:card>
</div>
