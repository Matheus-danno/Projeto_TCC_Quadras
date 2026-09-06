<div class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Pedidos da Loja') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Acompanhe as vendas e controle as retiradas presenciais.') }}</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50" :href="route('painel.loja')" wire:navigate>
                {{ __('Produtos') }}
            </flux:button>
            <flux:button variant="primary" color="orange" class="rounded-full" :href="route('painel.loja.pedidos')" wire:navigate>
                {{ __('Pedidos') }}
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <flux:input wire:model.live="busca" :label="__('Buscar por código de retirada ou cliente')" class="rounded-full" placeholder="Ex: K3F9P2" />

        <flux:select wire:model.live="filtroStatus" :label="__('Status')" class="rounded-full">
            <flux:select.option value="todos">{{ __('Todos') }}</flux:select.option>
            <flux:select.option value="aguardando">{{ __('Aguardando Retirada') }}</flux:select.option>
            <flux:select.option value="retirado">{{ __('Retirado') }}</flux:select.option>
            <flux:select.option value="cancelado">{{ __('Cancelado') }}</flux:select.option>
        </flux:select>
    </div>

    @if ($this->pedidos->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.clipboard-document-list class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhum pedido encontrado') }}</flux:heading>
            <flux:text>{{ __('Ajuste os filtros ou aguarde a primeira venda chegar.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="rounded-2xl p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Retirada') }}</flux:table.column>
                    <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                    <flux:table.column>{{ __('Itens') }}</flux:table.column>
                    <flux:table.column>{{ __('Total') }}</flux:table.column>
                    <flux:table.column>{{ __('Comissão (5%)') }}</flux:table.column>
                    <flux:table.column>{{ __('Você recebe') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Ações') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->pedidos as $pedido)
                        <flux:table.row wire:key="pedido-{{ $pedido->id }}">
                            <flux:table.cell class="ps-6! font-mono font-semibold text-zinc-900">{{ $pedido->numero_retirada }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $pedido->user->name }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">
                                {{ $pedido->itens->map(fn ($item) => $item->quantidade.'x '.$item->produto->nome)->join(', ') }}
                            </flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($pedido->total, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell class="text-red-500">- R$ {{ number_format($pedido->comissao_valor, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell class="font-semibold text-green-600">R$ {{ number_format($pedido->valorLiquido(), 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $pedido->status->corBadge() }}" size="sm">
                                    {{ $pedido->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($pedido->status === App\Enums\PedidoStatus::Aguardando)
                                    <div class="flex flex-col gap-1">
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-green-600 hover:underline"
                                            wire:click="marcarRetirado({{ $pedido->id }})"
                                        >
                                            {{ __('Marcar como Retirado') }}
                                        </button>
                                        <button
                                            type="button"
                                            class="text-xs font-medium text-red-500 hover:text-red-600 hover:underline"
                                            wire:click="cancelar({{ $pedido->id }})"
                                        >
                                            {{ __('Cancelar Pedido') }}
                                        </button>
                                    </div>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
</div>
