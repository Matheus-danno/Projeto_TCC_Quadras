<div class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Minhas Quadras') }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Gerencie as quadras que você cadastrou.') }}</flux:text>
        </div>

        <flux:button variant="primary" color="orange" icon="plus" class="rounded-2xl" :href="route('painel.quadras.criar')" wire:navigate>
            {{ __('Cadastrar Quadra') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <flux:input wire:model.live="busca" :label="__('Buscar por nome')" class="rounded-full" placeholder="Ex: Arena Sports" />

        <flux:select wire:model.live="filtroStatus" :label="__('Status')" class="rounded-full">
            <flux:select.option value="todas">{{ __('Todas') }}</flux:select.option>
            <flux:select.option value="ativa">{{ __('Ativas') }}</flux:select.option>
            <flux:select.option value="inativa">{{ __('Inativas') }}</flux:select.option>
        </flux:select>
    </div>

    @if ($this->quadras->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.map-pin class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma quadra encontrada') }}</flux:heading>
            <flux:text>{{ __('Ajuste os filtros ou cadastre uma nova quadra para começar a receber reservas.') }}</flux:text>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->quadras as $quadra)
                <flux:card class="flex flex-col gap-0 overflow-hidden rounded-2xl p-0" wire:key="quadra-{{ $quadra->id }}">
                    <div class="relative h-40 w-full overflow-hidden bg-zinc-100">
                        @if ($quadra->fotoCapa())
                            <img src="{{ $quadra->fotoCapa()->url() }}" alt="{{ $quadra->nome }}" class="size-full object-cover">
                        @else
                            <x-placeholder-pattern class="absolute inset-0 size-full stroke-zinc-300" />
                        @endif

                        <flux:badge color="{{ $quadra->ativa ? 'green' : 'red' }}" size="sm" class="absolute top-3 right-3">
                            {{ $quadra->ativa ? __('Ativa') : __('Inativa') }}
                        </flux:badge>
                    </div>

                    <div class="flex flex-col gap-4 p-5">
                        <div>
                            <flux:heading size="lg">{{ $quadra->nome }}</flux:heading>
                            <flux:text class="mt-1 text-zinc-400">
                                {{ $quadra->esporte->label() }} | {{ $quadra->cobertura ? __('Coberta') : __('Descoberta') }}
                            </flux:text>
                        </div>

                        <div>
                            <p class="font-semibold text-orange-500">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }} / {{ __('hora') }}</p>
                            <flux:text class="text-sm text-zinc-400">{{ trans_choice(':count reserva|:count reservas', $quadra->reservas_count, ['count' => $quadra->reservas_count]) }}</flux:text>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button
                                size="sm"
                                variant="outline"
                                class="flex-1 rounded-xl !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                                :href="route('painel.quadras.editar', $quadra)"
                                wire:navigate
                            >
                                {{ __('Editar') }}
                            </flux:button>

                            <flux:button
                                size="sm"
                                variant="primary"
                                color="orange"
                                class="flex-1 rounded-xl"
                                :href="route('painel.quadras.show', $quadra)"
                                wire:navigate
                            >
                                {{ __('Ver Detalhes') }}
                            </flux:button>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3">
                            @if ($quadra->ativa)
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-amber-600 hover:underline"
                                    wire:click="cancelar({{ $quadra->id }})"
                                >
                                    {{ __('Cancelar') }}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-green-600 hover:underline"
                                    wire:click="ativar({{ $quadra->id }})"
                                >
                                    {{ __('Ativar') }}
                                </button>
                            @endif

                            @unless ($quadra->temReservaFutura())
                                <button
                                    type="button"
                                    class="text-xs font-medium text-red-500 hover:text-red-600 hover:underline"
                                    wire:click="pedirExclusao({{ $quadra->id }})"
                                >
                                    {{ __('Excluir definitivamente') }}
                                </button>
                            @endunless
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

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
</div>
