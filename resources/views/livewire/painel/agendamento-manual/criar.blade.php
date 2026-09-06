<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Novo agendamento manual') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Registre uma reserva feita por telefone, WhatsApp ou presencialmente.') }}</flux:text>
    </div>

    @if ($this->quadras->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.calendar-days class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma quadra ativa disponível') }}</flux:heading>
            <flux:text>{{ __('Cadastre ou ative uma quadra em "Minhas Quadras" para poder agendar manualmente.') }}</flux:text>
        </flux:card>
    @else
        <form wire:submit="salvar" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:card class="flex flex-col gap-3 rounded-2xl">
                <flux:heading size="lg">{{ __('Dados do cliente') }}</flux:heading>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="$set('tipoCliente', 'existente')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $tipoCliente === 'existente' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                    >
                        {{ __('Cliente já cadastrado') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('tipoCliente', 'sem_conta')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $tipoCliente === 'sem_conta' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                    >
                        {{ __('Novo Cliente') }}
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
                                        <span class="text-sm text-zinc-500">
                                            {{ $cliente->email }}
                                            @if ($cliente->telefone)
                                                &middot; {{ $cliente->telefone }}
                                            @endif
                                        </span>
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
                        <flux:input wire:model="clienteNome" :label="__('Nome completo')" class="rounded-full" placeholder="Nome do cliente" />
                        <flux:input wire:model="clienteTelefone" :label="__('Telefone')" class="rounded-full" placeholder="(00) 00000-0000" />
                        <flux:input wire:model="clienteEmail" type="email" :label="__('E-mail (opcional)')" class="rounded-full md:col-span-2" placeholder="cliente@email.com" />
                    </div>
                @endif
            </flux:card>

            <flux:card class="flex flex-col gap-3 rounded-2xl">
                <flux:heading size="lg">{{ __('Observações (Opcional)') }}</flux:heading>

                <flux:textarea wire:model="observacoes" placeholder="Digite aqui!" rows="6" />
            </flux:card>

            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Detalhes da Reserva') }}</flux:heading>

                <flux:select wire:model="quadraId" :label="__('Quadra')" class="rounded-full">
                    <flux:select.option value="">{{ __('Escolha uma quadra') }}</flux:select.option>
                    @foreach ($this->quadras as $quadra)
                        <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <flux:input wire:model="data" type="date" :label="__('Data')" class="rounded-full" />
                    <flux:input wire:model.live="horaInicio" type="time" :label="__('Início')" class="rounded-full" />
                    <flux:input wire:model.live="horaFim" type="time" :label="__('Fim')" class="rounded-full" />
                </div>

                @if ($this->reservaConflitante)
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ __('Conflito: já existe uma reserva de :cliente às :inicio.', [
                            'cliente' => $this->reservaConflitante->nome_cliente,
                            'inicio' => substr($this->reservaConflitante->hora_inicio, 0, 5),
                        ]) }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input :label="__('Duração')" class="rounded-full" value="{{ $this->duracaoFormatada ?? '—' }}" disabled />
                    <flux:input wire:model="valor" type="number" step="0.01" min="0" :label="__('Valor')" class="rounded-full" placeholder="R$ 0,00" />
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Detalhes da Reserva') }}</flux:heading>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="$set('statusPagamento', 'pago')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $statusPagamento === 'pago' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                    >
                        {{ __('Pago') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('statusPagamento', 'pendente')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $statusPagamento === 'pendente' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                    >
                        {{ __('Pendente') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('statusPagamento', 'isento')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $statusPagamento === 'isento' ? 'bg-orange-500 text-white' : 'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50' }}"
                    >
                        {{ __('Isento') }}
                    </button>
                </div>

                @if ($statusPagamento !== 'isento')
                    <flux:select wire:model="formaPagamento" :label="__('Forma de Pagamento')" class="rounded-full">
                        <flux:select.option value="">{{ __('Escolha uma forma de pagamento') }}</flux:select.option>
                        <flux:select.option value="pix">{{ __('Pix') }}</flux:select.option>
                        <flux:select.option value="cartao">{{ __('Cartão') }}</flux:select.option>
                        <flux:select.option value="dinheiro">{{ __('Dinheiro') }}</flux:select.option>
                    </flux:select>
                @endif
            </flux:card>

            <div class="flex justify-end md:col-span-2">
                <flux:button type="submit" variant="primary" color="orange" class="rounded-full">
                    {{ __('Confirmar agendamento') }}
                </flux:button>
            </div>
        </form>
    @endif
</div>
