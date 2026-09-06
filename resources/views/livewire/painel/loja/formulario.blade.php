<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">
            {{ $produto ? __('Editar Produto') : __('Cadastrar Novo Produto') }}
        </flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">
            {{ $produto ? $produto->nome : __('Preencha as informações abaixo para colocar o produto à venda na loja.') }}
        </flux:text>
    </div>

    <form wire:submit="salvar" class="flex flex-col gap-6">
        <flux:card class="rounded-2xl">
            <flux:heading size="lg">{{ __('Foto do Produto') }}</flux:heading>
            <flux:text class="mb-4 text-zinc-400">{{ __('Uma imagem quadrada de boa qualidade ajuda a vender mais.') }}</flux:text>

            <div class="flex flex-wrap items-center gap-4">
                @if ($novaImagem)
                    <div class="relative h-32 w-32 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
                        <img src="{{ $novaImagem->temporaryUrl() }}" alt="" class="size-full object-cover">
                    </div>
                @elseif ($produto?->imagemUrl())
                    <div class="relative h-32 w-32 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
                        <img src="{{ $produto->imagemUrl() }}" alt="" class="size-full object-cover">

                        <button
                            type="button"
                            wire:click="removerImagemAtual"
                            class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                            aria-label="{{ __('Remover foto') }}"
                        >
                            <flux:icon.x-mark class="size-3.5" />
                        </button>
                    </div>
                @endif

                <label class="flex h-32 w-32 shrink-0 cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-orange-300 text-orange-500 hover:bg-orange-50">
                    <input type="file" accept="image/*" wire:model="novaImagem" class="hidden">
                    <flux:icon.plus class="size-5" />
                    <span class="text-xs font-semibold">{{ __('Trocar foto') }}</span>
                </label>
            </div>

            @error('novaImagem')
                <flux:text class="mt-3 text-red-600">{{ $message }}</flux:text>
            @enderror
        </flux:card>

        <flux:card class="rounded-2xl">
            <flux:heading size="lg" class="mb-4">{{ __('Informações do Produto') }}</flux:heading>

            <div class="flex flex-col gap-4">
                <flux:input wire:model="nome" :label="__('Nome do Produto')" class="rounded-xl" placeholder="Ex: Bola de Futevôlei" required />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="categoria" :label="__('Categoria')" class="rounded-xl" placeholder="Ex: Acessórios" required />

                    <flux:select wire:model="ativo" :label="__('Status')" class="rounded-xl" required>
                        <flux:select.option value="1">{{ __('Ativo (à venda na loja)') }}</flux:select.option>
                        <flux:select.option value="0">{{ __('Inativo (oculto da loja)') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="preco" :label="__('Preço (R$)')" class="rounded-xl" type="number" step="0.01" min="0" placeholder="49,90" required />
                    <flux:input wire:model="estoque" :label="__('Estoque disponível')" class="rounded-xl" type="number" min="0" placeholder="Nº de unidades" required />
                </div>

                <flux:textarea wire:model="descricao" :label="__('Descrição')" class="rounded-xl" rows="3" placeholder="Descreva o produto para os jogadores" required />
            </div>
        </flux:card>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if ($produto)
                <flux:button
                    type="button"
                    variant="outline"
                    class="rounded-xl !border-red-300 !text-red-600 hover:!bg-red-50"
                    wire:click="pedirExclusao"
                >
                    {{ __('Excluir Produto') }}
                </flux:button>
            @endif

            <div class="flex flex-col gap-3 sm:flex-row">
                <flux:button
                    :href="$produto ? route('painel.loja.show', $produto) : route('painel.loja')"
                    variant="outline"
                    class="rounded-xl !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                    wire:navigate
                >
                    {{ __('Cancelar') }}
                </flux:button>

                <flux:button type="submit" variant="primary" color="orange" class="rounded-xl">
                    {{ $produto ? __('Salvar Alterações') : __('Cadastrar Produto') }}
                </flux:button>
            </div>
        </div>
    </form>

    @if ($produto)
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
    @endif
</div>
