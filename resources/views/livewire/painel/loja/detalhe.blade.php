<div class="flex w-full flex-col gap-6">
    <div class="flex flex-col gap-3">
        <flux:link :href="route('painel.loja')" class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-orange-600! no-underline hover:underline" wire:navigate>
            {{ __('← Voltar para minha loja') }}
        </flux:link>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ $produto->nome }}</flux:heading>
                <flux:badge color="{{ $produto->ativo ? 'green' : 'red' }}" size="sm">
                    {{ $produto->ativo ? __('Ativo') : __('Inativo') }}
                </flux:badge>
            </div>

            <div class="flex items-center gap-2">
                @if ($produto->ativo)
                    <flux:button
                        variant="outline"
                        class="rounded-xl !border-red-300 !text-red-600 hover:!bg-red-50"
                        wire:click="desativar"
                    >
                        {{ __('Desativar') }}
                    </flux:button>
                @else
                    <flux:button
                        variant="outline"
                        class="rounded-xl !border-green-300 !text-green-600 hover:!bg-green-50"
                        wire:click="ativar"
                    >
                        {{ __('Ativar') }}
                    </flux:button>
                @endif

                <flux:button :href="route('painel.loja.editar', $produto)" variant="primary" color="orange" class="rounded-xl" wire:navigate>
                    {{ __('Editar Produto') }}
                </flux:button>
            </div>
        </div>
        <flux:text class="text-base text-zinc-400">{{ $produto->categoria }}</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flux:card class="rounded-2xl">
            <flux:heading size="lg">{{ __('Sobre o produto') }}</flux:heading>

            <div class="mt-3 aspect-square w-32 overflow-hidden rounded-xl bg-zinc-100">
                @if ($produto->imagemUrl())
                    <img src="{{ $produto->imagemUrl() }}" alt="{{ $produto->nome }}" class="size-full object-cover">
                @else
                    <x-placeholder-pattern class="size-full stroke-zinc-300" />
                @endif
            </div>

            <flux:text class="mt-3">R$ {{ number_format($produto->preco, 2, ',', '.') }}</flux:text>

            @if ($produto->descricao)
                <flux:text class="mt-1 text-zinc-500">{{ $produto->descricao }}</flux:text>
            @endif
        </flux:card>

        <flux:card class="flex flex-col divide-y divide-zinc-100 rounded-2xl p-0">
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Unidades vendidas') }}</flux:text>
                <flux:heading size="md">{{ $this->indicadores['unidadesVendidas'] }}</flux:heading>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Receita Gerada') }}</flux:text>
                <flux:heading size="md">R$ {{ number_format($this->indicadores['receitaGerada'], 2, ',', '.') }}</flux:heading>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Estoque atual') }}</flux:text>
                <flux:heading size="md">{{ $this->indicadores['estoqueAtual'] }}</flux:heading>
            </div>
        </flux:card>
    </div>

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">{{ __('Vendas Recentes') }}</flux:heading>

        @if ($this->vendasRecentes->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.shopping-bag class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma venda encontrada') }}</flux:heading>
                <flux:text>{{ __('Este produto ainda não foi vendido.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="rounded-2xl p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6!">{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Data') }}</flux:table.column>
                        <flux:table.column>{{ __('Qtd.') }}</flux:table.column>
                        <flux:table.column>{{ __('Valor') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->vendasRecentes as $item)
                            <flux:table.row wire:key="venda-{{ $item->id }}">
                                <flux:table.cell class="ps-6! font-semibold text-zinc-900">{{ $item->pedido->user->name }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $item->created_at->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $item->quantidade }}</flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">
                                    R$ {{ number_format($item->quantidade * $item->preco_unitario, 2, ',', '.') }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="{{ match ($item->pedido->status) {
                                        App\Enums\PedidoStatus::Confirmado => 'green',
                                        App\Enums\PedidoStatus::Pendente => 'amber',
                                        App\Enums\PedidoStatus::Cancelado => 'red',
                                    } }}" size="sm">
                                        {{ $item->pedido->status->label() }}
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
