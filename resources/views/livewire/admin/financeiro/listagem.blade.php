<div class="flex w-full flex-col gap-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Financeiro') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Todas as reservas da plataforma e a comissão gerada por elas.') }}</flux:text>
        </div>

        <flux:button :href="route('admin.financeiro.repasses')" variant="outline" class="rounded-full" wire:navigate>
            {{ __('Repasses aos proprietários') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Volume no período') }}</flux:text>
            <flux:heading size="xl">R$ {{ number_format($this->resumoPeriodo['volumeTotal'], 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Comissão do admin') }}</flux:text>
            <flux:heading size="xl" class="text-orange-600">R$ {{ number_format($this->resumoPeriodo['comissaoTotal'], 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Valor dos proprietários') }}</flux:text>
            <flux:heading size="xl" class="text-green-600">R$ {{ number_format($this->resumoPeriodo['valorProprietarios'], 2, ',', '.') }}</flux:heading>
        </flux:card>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex flex-col gap-3 p-6 pb-0 lg:flex-row lg:items-center lg:justify-between">
            <flux:heading size="lg">{{ __('Reservas') }}</flux:heading>

            <div class="flex flex-wrap gap-2">
                <flux:input wire:model.live.debounce.400ms="busca" placeholder="{{ __('Buscar cliente ou quadra...') }}" class="rounded-full sm:w-52" />

                <flux:select wire:model.live="periodo" class="rounded-full sm:w-40">
                    <flux:select.option value="hoje">{{ __('Hoje') }}</flux:select.option>
                    <flux:select.option value="7dias">{{ __('Últimos 7 dias') }}</flux:select.option>
                    <flux:select.option value="mes_atual">{{ __('Este mês') }}</flux:select.option>
                    <flux:select.option value="mes_anterior">{{ __('Mês anterior') }}</flux:select.option>
                    <flux:select.option value="ano_atual">{{ __('Este ano') }}</flux:select.option>
                    <flux:select.option value="personalizado">{{ __('Personalizado') }}</flux:select.option>
                </flux:select>

                @if ($periodo === 'personalizado')
                    <flux:input type="date" wire:model.live="inicioPersonalizado" class="rounded-full" />
                    <flux:input type="date" wire:model.live="fimPersonalizado" class="rounded-full" />
                @endif

                <flux:select wire:model.live="filtroQuadraId" class="rounded-full sm:w-40">
                    <flux:select.option value="">{{ __('Todas as quadras') }}</flux:select.option>
                    @foreach ($this->quadras as $quadra)
                        <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filtroDonoId" class="rounded-full sm:w-40">
                    <flux:select.option value="">{{ __('Todos os proprietários') }}</flux:select.option>
                    @foreach ($this->donos as $dono)
                        <flux:select.option value="{{ $dono->id }}">{{ $dono->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filtroStatus" class="rounded-full sm:w-36">
                    <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
                    @foreach ($statusDisponiveis as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:button wire:click="exportarCsv" variant="outline" class="rounded-full">
                    {{ __('Exportar CSV') }}
                </flux:button>
            </div>
        </div>

        <div wire:loading.class="opacity-60" wire:target="busca,periodo,inicioPersonalizado,fimPersonalizado,filtroQuadraId,filtroDonoId,filtroStatus">
            @if ($this->reservas->isEmpty())
                <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                    <flux:icon.banknotes class="size-8 text-zinc-300" />
                    <flux:heading size="lg">{{ __('Nenhuma reserva encontrada') }}</flux:heading>
                    <flux:text>{{ __('Ajuste a busca, o período ou os filtros selecionados.') }}</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6!">{{ __('Data') }}</flux:table.column>
                        <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                        <flux:table.column>{{ __('Proprietário') }}</flux:table.column>
                        <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Valor total') }}</flux:table.column>
                        <flux:table.column>{{ __('Comissão (admin)') }}</flux:table.column>
                        <flux:table.column>{{ __('Proprietário recebe') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->reservas as $reserva)
                            @php $status = $this->statusExibicao($reserva); @endphp
                            <flux:table.row wire:key="financeiro-reserva-{{ $reserva->id }}">
                                <flux:table.cell class="ps-6! text-zinc-500">{{ $reserva->data->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">{{ $reserva->quadra->nome }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $reserva->quadra->dono?->name ?? '—' }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $reserva->nome_cliente }}</flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($this->valorTotalReais($reserva), 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell class="text-orange-600">R$ {{ number_format($this->comissaoReais($reserva), 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell class="text-green-600">R$ {{ number_format($this->valorProprietarioReais($reserva), 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ $status['color'] }}">{{ $status['label'] }}</flux:badge>
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
