<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">
            {{ $quadra ? __('Editar Quadra') : __('Cadastrar Nova Quadra') }}
        </flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">
            {{ $quadra ? $quadra->nome : __('Preencha as informações abaixo para cadastrar sua quadra na plataforma.') }}
        </flux:text>
    </div>

    <form wire:submit="salvar" class="flex flex-col gap-6">
        <flux:card class="rounded-2xl">
            <flux:heading size="lg">{{ __('Fotos da Quadra') }}</flux:heading>
            <flux:text class="mb-4 text-zinc-400">
                {{ $quadra ? __('A primeira foto é a capa. Passe o mouse para remover.') : __('Adicione até 8 fotos. A primeira será a foto de capa.') }}
            </flux:text>

            <div class="flex flex-wrap gap-4">
                @if ($this->totalFotos() < 8)
                    <label class="flex h-32 w-28 shrink-0 cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-orange-300 text-orange-500 hover:bg-orange-50">
                        <input type="file" multiple accept="image/*" wire:model="novasFotos" class="hidden">
                        <flux:icon.plus class="size-5" />
                        <span class="text-xs font-semibold">{{ __('Adicionar foto') }}</span>
                    </label>
                @endif

                @foreach ($this->fotosExistentes() as $indice => $foto)
                    <div class="relative h-32 w-28 shrink-0 overflow-hidden rounded-xl bg-zinc-100" wire:key="foto-existente-{{ $foto->id }}">
                        <img src="{{ $foto->url() }}" alt="" class="size-full object-cover">

                        @if ($indice === 0)
                            <flux:badge color="orange" size="sm" class="absolute top-2 left-2">{{ __('Capa') }}</flux:badge>
                        @endif

                        <button
                            type="button"
                            wire:click="removerFotoExistente({{ $foto->id }})"
                            class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                            aria-label="{{ __('Remover foto') }}"
                        >
                            <flux:icon.x-mark class="size-3.5" />
                        </button>
                    </div>
                @endforeach

                @foreach ($novasFotos as $indice => $foto)
                    <div class="relative h-32 w-28 shrink-0 overflow-hidden rounded-xl bg-zinc-100" wire:key="foto-nova-{{ $indice }}">
                        <img src="{{ $foto->temporaryUrl() }}" alt="" class="size-full object-cover">

                        @if ($this->fotosExistentes()->isEmpty() && $indice === 0)
                            <flux:badge color="orange" size="sm" class="absolute top-2 left-2">{{ __('Capa') }}</flux:badge>
                        @endif

                        <button
                            type="button"
                            wire:click="removerNovaFoto({{ $indice }})"
                            class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                            aria-label="{{ __('Remover foto') }}"
                        >
                            <flux:icon.x-mark class="size-3.5" />
                        </button>
                    </div>
                @endforeach
            </div>

            @error('novasFotos')
                <flux:text class="mt-3 text-red-600">{{ $message }}</flux:text>
            @enderror
            @error('novasFotos.*')
                <flux:text class="mt-3 text-red-600">{{ $message }}</flux:text>
            @enderror
        </flux:card>

        <flux:card class="rounded-2xl">
            <flux:heading size="lg" class="mb-4">{{ __('Informações da Quadra') }}</flux:heading>

            <div class="flex flex-col gap-4">
                <flux:input wire:model="nome" :label="__('Nome da Quadra')" class="rounded-xl" placeholder="Ex: Arena Sports - Quadra 1" required />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:select wire:model="esporte" :label="__('Tipo de Esporte')" class="rounded-xl" required>
                        <flux:select.option value="">{{ __('Selecione') }}</flux:select.option>
                        @foreach ($esportes as $opcao)
                            <flux:select.option value="{{ $opcao->value }}">{{ $opcao->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="cobertura" :label="__('Cobertura')" class="rounded-xl" required>
                        <flux:select.option value="0">{{ __('Descoberta') }}</flux:select.option>
                        <flux:select.option value="1">{{ __('Coberta') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="valor_hora" :label="__('Valor por Hora (R$)')" class="rounded-xl" type="number" step="0.01" min="0" placeholder="50,00" required />
                    <flux:input wire:model="capacidade_maxima" :label="__('Capacidade Máxima')" class="rounded-xl" type="number" min="1" placeholder="Nº de jogadores" required />
                </div>

                <flux:input wire:model="endereco" :label="__('Endereço da Quadra')" class="rounded-xl" placeholder="Rua, número - Bairro" required />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="cidade" :label="__('Cidade')" class="rounded-xl" placeholder="Bauru" required />
                    <flux:input wire:model="cep" :label="__('CEP')" class="rounded-xl" placeholder="00000-000" required />
                </div>

                <flux:input wire:model="bairro" :label="__('Bairro')" class="rounded-xl" placeholder="Ex: Centro" required />

                <flux:textarea wire:model="descricao" :label="__('Descrição / Comodidades')" class="rounded-xl" rows="3" placeholder="Ex: Quadra de Areia | Descoberta | Bar | Banheiro | Vestiário" required />
            </div>
        </flux:card>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if ($quadra)
                <flux:button
                    type="button"
                    variant="outline"
                    class="rounded-xl !border-red-300 !text-red-600 hover:!bg-red-50"
                    wire:click="pedirExclusao"
                >
                    {{ __('Excluir Quadra') }}
                </flux:button>
            @endif

            <div class="flex flex-col gap-3 sm:flex-row">
                <flux:button
                    :href="$quadra ? route('painel.quadras.show', $quadra) : route('painel.quadras')"
                    variant="outline"
                    class="rounded-xl !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                    wire:navigate
                >
                    {{ __('Cancelar') }}
                </flux:button>

                <flux:button type="submit" variant="primary" color="orange" class="rounded-xl">
                    {{ $quadra ? __('Salvar Alterações') : __('Cadastrar Quadra') }}
                </flux:button>
            </div>
        </div>
    </form>

    @if ($quadra)
        <flux:modal name="excluir-quadra" class="w-full md:w-96">
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Excluir quadra') }}</flux:heading>

                @if ($bloqueioExclusao)
                    <flux:text>{{ $bloqueioExclusao }}</flux:text>

                    <div class="flex justify-end">
                        <flux:modal.close>
                            <flux:button variant="primary" color="orange" class="rounded-full">{{ __('Entendi') }}</flux:button>
                        </flux:modal.close>
                    </div>
                @else
                    <flux:text>{{ __('Tem certeza que deseja excluir esta quadra? Essa ação não pode ser desfeita.') }}</flux:text>

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
