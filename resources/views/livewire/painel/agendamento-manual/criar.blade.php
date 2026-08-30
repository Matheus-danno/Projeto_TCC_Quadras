<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Agendamento Manual') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Registre uma reserva diretamente, sem que o cliente precise passar pelo site.') }}</flux:text>
    </div>

    @if ($this->quadras->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.calendar-days class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma quadra ativa disponível') }}</flux:heading>
            <flux:text>{{ __('Cadastre ou ative uma quadra em "Minhas Quadras" para poder agendar manualmente.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="rounded-2xl">
            <form wire:submit="salvar" class="flex flex-col gap-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:select wire:model="quadraId" :label="__('Quadra')" class="rounded-full">
                        <flux:select.option value="">{{ __('Selecione') }}</flux:select.option>
                        @foreach ($this->quadras as $quadra)
                            <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="data" type="date" :label="__('Data')" class="rounded-full" />

                    <flux:input wire:model="horaInicio" type="time" :label="__('Início')" class="rounded-full" />
                    <flux:input wire:model="horaFim" type="time" :label="__('Fim')" class="rounded-full" />
                </div>

                <div class="flex flex-col gap-3">
                    <flux:heading size="lg">{{ __('Cliente') }}</flux:heading>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="$set('tipoCliente', 'existente')"
                            class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $tipoCliente === 'existente' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                        >
                            {{ __('Cliente cadastrado') }}
                        </button>
                        <button
                            type="button"
                            wire:click="$set('tipoCliente', 'sem_conta')"
                            class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $tipoCliente === 'sem_conta' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                        >
                            {{ __('Sem conta no sistema') }}
                        </button>
                    </div>

                    @if ($tipoCliente === 'existente')
                        <div>
                            <flux:input
                                wire:model.live="buscaCliente"
                                :label="__('Buscar cliente por nome ou e-mail')"
                                class="rounded-full"
                                placeholder="Ex: João Silva"
                                autocomplete="off"
                            />

                            @if ($clienteId)
                                <div class="mt-2 flex items-center gap-2">
                                    <flux:badge color="green" size="sm">{{ __('Cliente selecionado') }}</flux:badge>
                                    <flux:text>{{ $buscaCliente }}</flux:text>
                                </div>
                            @elseif ($this->clientesEncontrados->isNotEmpty())
                                <div class="mt-2 flex flex-col divide-y divide-zinc-100 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                                    @foreach ($this->clientesEncontrados as $cliente)
                                        <button
                                            type="button"
                                            wire:click="selecionarCliente({{ $cliente->id }})"
                                            class="flex flex-col items-start px-4 py-2 text-left hover:bg-orange-50"
                                        >
                                            <span class="font-semibold text-zinc-900">{{ $cliente->name }}</span>
                                            <span class="text-sm text-zinc-500">{{ $cliente->email }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif (mb_strlen(trim($buscaCliente)) >= 2)
                                <flux:text class="mt-2 text-zinc-500">{{ __('Nenhum cliente encontrado.') }}</flux:text>
                            @endif

                            @error('clienteId')
                                <flux:text class="mt-2 text-red-600">{{ $message }}</flux:text>
                            @enderror
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <flux:input wire:model="clienteNome" :label="__('Nome do cliente')" class="rounded-full" placeholder="Ex: Maria Souza" />
                            <flux:input wire:model="clienteTelefone" :label="__('Telefone do cliente')" class="rounded-full" placeholder="(11) 91234-5678" />
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" color="orange" class="rounded-full">
                        {{ __('Agendar Reserva') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif
</div>
