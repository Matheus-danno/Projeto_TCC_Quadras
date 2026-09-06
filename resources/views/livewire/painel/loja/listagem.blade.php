<div class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Minha Loja') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Cadastre e gerencie os produtos que você vende.') }}</flux:text>
        </div>

        <flux:button variant="primary" color="orange" icon="plus" class="rounded-2xl" :href="route('painel.loja.criar')" wire:navigate>
            {{ __('Cadastrar Produto') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Unidades vendidas') }}</flux:text>
            <flux:heading size="xl">{{ $this->resumoVendas['unidades'] }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Receita gerada') }}</flux:text>
            <flux:heading size="xl" class="text-orange-500">R$ {{ number_format($this->resumoVendas['receita'], 2, ',', '.') }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <flux:input wire:model.live="busca" :label="__('Buscar por nome')" class="rounded-full" placeholder="Ex: Bola de Futevôlei" />

        <flux:select wire:model.live="filtroStatus" :label="__('Status')" class="rounded-full">
            <flux:select.option value="todos">{{ __('Todos') }}</flux:select.option>
            <flux:select.option value="ativo">{{ __('Ativos') }}</flux:select.option>
            <flux:select.option value="inativo">{{ __('Inativos') }}</flux:select.option>
        </flux:select>
    </div>

    @if ($this->produtos->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.shopping-bag class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhum produto encontrado') }}</flux:heading>
            <flux:text>{{ __('Ajuste os filtros ou cadastre um novo produto para começar a vender.') }}</flux:text>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->produtos as $produto)
                <flux:card class="flex flex-col gap-0 overflow-hidden rounded-2xl p-0" wire:key="produto-{{ $produto->id }}">
                    <div class="relative h-40 w-full overflow-hidden bg-zinc-100">
                        @if ($produto->imagemUrl())
                            <img src="{{ $produto->imagemUrl() }}" alt="{{ $produto->nome }}" class="size-full object-cover">
                        @else
                            <x-placeholder-pattern class="absolute inset-0 size-full stroke-zinc-300" />
                        @endif

                        <flux:badge color="{{ $produto->ativo ? 'green' : 'red' }}" size="sm" class="absolute top-3 right-3">
                            {{ $produto->ativo ? __('Ativo') : __('Inativo') }}
                        </flux:badge>
                    </div>

                    <div class="flex flex-col gap-4 p-5">
                        <div>
                            <flux:heading size="lg">{{ $produto->nome }}</flux:heading>
                            <flux:text class="mt-1 text-zinc-400">
                                {{ $produto->categoria }} | {{ trans_choice(':count em estoque|:count em estoque', $produto->estoque, ['count' => $produto->estoque]) }}
                            </flux:text>
                        </div>

                        <div>
                            <p class="font-semibold text-orange-500">R$ {{ number_format($produto->preco, 2, ',', '.') }}</p>
                            <flux:text class="text-sm text-zinc-400">
                                {{ trans_choice(':count unidade vendida|:count unidades vendidas', $produto->unidades_vendidas ?? 0, ['count' => $produto->unidades_vendidas ?? 0]) }}
                            </flux:text>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button
                                size="sm"
                                variant="outline"
                                class="flex-1 rounded-xl !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                                :href="route('painel.loja.editar', $produto)"
                                wire:navigate
                            >
                                {{ __('Editar') }}
                            </flux:button>

                            <flux:button
                                size="sm"
                                variant="primary"
                                color="orange"
                                class="flex-1 rounded-xl"
                                :href="route('painel.loja.show', $produto)"
                                wire:navigate
                            >
                                {{ __('Ver Detalhes') }}
                            </flux:button>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3">
                            @if ($produto->ativo)
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-amber-600 hover:underline"
                                    wire:click="desativar({{ $produto->id }})"
                                >
                                    {{ __('Desativar Produto') }}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-green-600 hover:underline"
                                    wire:click="ativar({{ $produto->id }})"
                                >
                                    {{ __('Ativar') }}
                                </button>
                            @endif

                            <button
                                type="button"
                                class="text-xs font-medium text-red-500 hover:text-red-600 hover:underline"
                                wire:click="pedirExclusao({{ $produto->id }})"
                            >
                                {{ __('Excluir definitivamente') }}
                            </button>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    <flux:modal name="excluir-produto" class="w-full md:w-96">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Excluir produto') }}</flux:heading>

            @if ($bloqueioExclusao)
                <flux:text>{{ $bloqueioExclusao }}</flux:text>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="primary" color="orange" class="rounded-full">{{ __('Entendi') }}</flux:button>
                    </flux:modal.close>
                </div>
            @else
                <flux:text>{{ __('Tem certeza que deseja excluir este produto? Essa ação não pode ser desfeita.') }}</flux:text>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                            {{ __('Cancelar') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" class="rounded-full" wire:click="excluir">{{ __('Excluir') }}</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
